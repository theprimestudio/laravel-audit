<?php

namespace ThePrimeStudio\Audit\Managers;

use ThePrimeStudio\Audit\Models\AuditRun;
use ThePrimeStudio\Audit\Contracts\AuditCheck;
use ThePrimeStudio\Audit\Support\AuditContextImpl;
use Illuminate\Support\Facades\Http;

class CheckManager
{
    protected array $checks = [];

    public function __construct($app)
    {
        $this->registerDefaultChecks();
    }

    public function register(AuditCheck $check): void
    {
        $this->checks[] = $check;
    }

    protected function registerDefaultChecks(): void
    {
        // Default check classes will be loaded here
        $this->register(new \ThePrimeStudio\Audit\Engines\Seo\MetaTagsCheck());
        $this->register(new \ThePrimeStudio\Audit\Engines\Security\HeadersCheck());
        $this->register(new \ThePrimeStudio\Audit\Engines\Laravel\LaravelConfigurationCheck());
    }

    public function execute(AuditRun $run): void
    {
        // Count total URLs upfront with a lightweight query
        $totalUrls = $run->urls()->where('is_external', false)->count();
        $processedCount = 0;

        // Process URLs in chunks to avoid loading them all into memory at once
        $run->urls()->where('is_external', false)->chunkById(25, function ($urls) use ($run, $totalUrls, &$processedCount) {
            foreach ($urls as $auditUrl) {
                $processedCount++;

                // Report progress: audit phase is 42%-80%
                if ($processedCount % 5 === 0 || $processedCount === 1) {
                    $auditPercent = $totalUrls > 0
                        ? (int) (42 + ($processedCount / $totalUrls) * 38)
                        : 42;
                    $run->update([
                        'progress_percent' => min(80, $auditPercent),
                        'progress_message' => "Audits: Checking URL {$processedCount}/{$totalUrls} — SEO, Security & Performance...",
                    ]);
                }

                $this->processUrl($run, $auditUrl);
            }
        });
    }

    /**
     * Process a single URL: fetch content, run checks, create results, and free memory.
     */
    protected function processUrl(AuditRun $run, $auditUrl): void
    {
        $html = '';
        $response = null;

        try {
            $response = Http::withoutVerifying()
                ->timeout(15)
                ->withOptions(['cookies' => false])
                ->withHeaders([
                    'User-Agent' => 'Laravel-Audit-Crawler/1.0',
                    'X-Audit-Run-Id' => $run->id,
                ])
                ->get($auditUrl->url);
            if ($response->successful()) {
                $html = $response->body();
            }
        } catch (\Exception $e) {
            // Ignore download failures
        }

        $dom = new \DOMDocument();
        if (!empty($html)) {
            libxml_use_internal_errors(true);
            @$dom->loadHTML($html);
            libxml_clear_errors();
        }

        // --- SEO Results ---
        $titles = $dom->getElementsByTagName('title');
        $titleText = $titles->length > 0 ? trim($titles->item(0)->textContent) : null;

        $metas = $dom->getElementsByTagName('meta');
        $descriptionText = null;
        $robotsMeta = null;
        $hasOg = false;
        $hasTwitter = false;
        foreach ($metas as $meta) {
            $metaName = strtolower($meta->getAttribute('name'));
            $metaProperty = strtolower($meta->getAttribute('property'));
            if ($metaName === 'description') {
                $descriptionText = trim($meta->getAttribute('content'));
            }
            if ($metaName === 'robots') {
                $robotsMeta = trim($meta->getAttribute('content'));
            }
            if (str_starts_with($metaProperty, 'og:')) {
                $hasOg = true;
            }
            if (str_starts_with($metaName, 'twitter:')) {
                $hasTwitter = true;
            }
        }

        $links = $dom->getElementsByTagName('link');
        $canonicalUrl = null;
        foreach ($links as $link) {
            if (strtolower($link->getAttribute('rel')) === 'canonical') {
                $canonicalUrl = trim($link->getAttribute('href'));
            }
        }

        $h1Headings = [];
        $h1Elements = $dom->getElementsByTagName('h1');
        foreach ($h1Elements as $h1) {
            $h1Headings[] = trim($h1->textContent);
        }

        \ThePrimeStudio\Audit\Models\AuditSeoResult::create([
            'audit_url_id' => $auditUrl->id,
            'title' => $titleText,
            'meta_description' => $descriptionText,
            'canonical_url' => $canonicalUrl,
            'robots_meta' => $robotsMeta,
            'h1_headings' => $h1Headings,
            'schema_markup' => [],
            'has_open_graph' => $hasOg,
            'has_twitter_card' => $hasTwitter,
        ]);

        // --- Security Results ---
        \ThePrimeStudio\Audit\Models\AuditSecurityResult::create([
            'audit_url_id' => $auditUrl->id,
            'is_https' => str_starts_with($auditUrl->url, 'https://'),
            'hsts_enabled' => $response ? !empty($response->header('Strict-Transport-Security')) : false,
            'csp_header' => $response ? $response->header('Content-Security-Policy') : null,
            'x_frame_options' => $response ? $response->header('X-Frame-Options') : null,
            'x_content_type_options' => $response ? $response->header('X-Content-Type-Options') : null,
            'referrer_policy' => $response ? $response->header('Referrer-Policy') : null,
            'permissions_policy' => $response ? $response->header('Permissions-Policy') : null,
        ]);

        // --- Run registered checks ---
        $context = new AuditContextImpl($auditUrl->url, $response, $dom);
        $seoIssuesCount = 0;

        foreach ($this->checks as $check) {
            try {
                $results = $check->run($context);

                foreach ($results as $result) {
                    if ($result['failed'] ?? false) {
                        if ($check->category() === 'seo') {
                            $seoIssuesCount++;
                        }
                        $run->issues()->create([
                            'category' => $check->category(),
                            'type' => $result['type'],
                            'severity' => $result['severity'] ?? 'medium',
                            'url' => $auditUrl->url,
                            'title' => $result['title'],
                            'description' => $result['description'],
                            'recommendation' => $result['recommendation'] ?? null,
                            'status' => 'open',
                            'first_detected_at' => now(),
                        ]);
                    }
                }
            } catch (\Exception $e) {
                // Fail-safe per check
            }
        }

        if ($seoIssuesCount > 0) {
            \ThePrimeStudio\Audit\Models\AuditSeoResult::where('audit_url_id', $auditUrl->id)
                ->update(['issues_count' => $seoIssuesCount]);
        }

        // --- Free memory explicitly ---
        unset($html, $dom, $response, $context);
    }
}
