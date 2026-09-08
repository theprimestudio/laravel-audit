<?php

namespace ThePrimeStudio\Audit\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;
use ThePrimeStudio\Audit\Models\AuditRun;
use ThePrimeStudio\Audit\Models\AuditUrl;
use ThePrimeStudio\Audit\Models\AuditHttpResult;

class CrawlerService
{
    protected string $baseUrl;
    protected array $crawled = [];
    protected array $queue = [];
    protected array $queued = []; // O(1) lookup for URLs already in queue
    protected int $maxUrls;
    protected int $timeout;
    protected array $excludes = [];
    protected array $robotsExcludes = [];

    /** File extensions to skip during crawling */
    protected array $skipExtensions = [
        'css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico', 'webp', 'avif',
        'woff', 'woff2', 'ttf', 'eot', 'otf',
        'pdf', 'zip', 'rar', 'gz', 'tar',
        'mp3', 'mp4', 'avi', 'mov', 'wmv', 'flv', 'webm',
        'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
        'map', 'json', 'xml', 'txt',
    ];

    public function __construct()
    {
        $this->maxUrls = config('audit.crawler.max_urls', 100);
        $this->timeout = config('audit.crawler.timeout', 30);
        $customExcludes = config('audit.crawler.exclude_urls', '');
        $this->excludes = empty($customExcludes) ? [] : array_filter(array_map('trim', explode(',', $customExcludes)));
    }

    public function crawl(AuditRun $run, string $baseUrl)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->queue[] = [
            'url' => $this->baseUrl,
            'depth' => 0,
            'source_url' => 'Initial Base URL',
        ];

        $this->dynamicallyExcludeAuthRoutes();
        $this->parseRobotsTxt();

        // Seed registered Laravel routes into the crawl queue
        if (config('audit.crawler.seed_laravel_routes', true)) {
            $this->seedRegisteredRoutes();
        }

        $totalQueued = count($this->queue);

        while (!empty($this->queue) && count($this->crawled) < $this->maxUrls) {
            $current = array_shift($this->queue);
            $url = $current['url'];
            $depth = $current['depth'];
            $sourceUrl = $current['source_url'] ?? null;

            // Normalize URL to match base scheme
            $url = $this->normalizeScheme($url);

            if (isset($this->crawled[$url])) {
                continue;
            }

            if ($this->isExcluded($url)) {
                continue;
            }

            if ($this->isStaticAsset($url)) {
                continue;
            }

            $this->crawled[$url] = true;
            $crawledCount = count($this->crawled);

            // Report progress every 5 URLs (crawl phase is 2%-40%, so scale within that range)
            if ($crawledCount % 5 === 0 || $crawledCount === 1) {
                $totalDiscovered = count($this->crawled) + count($this->queue);
                $crawlPercent = min(38, (int) (($crawledCount / max(1, $totalDiscovered)) * 38)) + 2;
                $run->update([
                    'progress_percent' => $crawlPercent,
                    'progress_message' => "Crawling: {$crawledCount} processed, " . count($this->queue) . " in queue...",
                    'urls_crawled' => $crawledCount,
                ]);
            }

            \Illuminate\Support\Facades\Log::info("Crawler visiting URL: {$url}");

            $headers = array_merge([
                'User-Agent' => 'Laravel-Audit-Crawler/1.0',
                'X-Audit-Run-Id' => $run->id,
            ], config('audit.crawler.headers', []));

            $startTime = microtime(true);
            try {
                $response = Http::withoutVerifying()
                    ->timeout($this->timeout)
                    ->withOptions(['cookies' => false])
                    ->withHeaders($headers)
                    ->get($url);
                $duration = (int) ((microtime(true) - $startTime) * 1000);
                
                // Track URL in DB
                $auditUrl = AuditUrl::create([
                    'audit_run_id' => $run->id,
                    'url' => $url,
                    'path' => parse_url($url, PHP_URL_PATH) ?: '/',
                    'is_external' => false,
                    'source_url' => $sourceUrl,
                    'discovered_depth' => $depth,
                ]);

                // Track Http Result
                $body = $response->body();
                $bodySize = strlen($body);
                $contentType = $response->header('Content-Type');

                AuditHttpResult::create([
                    'audit_url_id' => $auditUrl->id,
                    'status_code' => $response->status(),
                    'response_time_ms' => $duration,
                    'ttfb_ms' => (int) ($duration * 0.4),
                    'response_size_bytes' => $bodySize,
                    'content_type' => $contentType,
                    'headers' => $response->headers(),
                ]);

                // Crawl pages found in HTML if depth is reasonable
                if ($response->successful() && Str::contains($contentType, 'text/html')) {
                    $this->discoverLinks($run, $auditUrl, $body, $depth + 1);
                }

                // Free memory: release response body and response object
                unset($body, $response);

            } catch (\Exception $e) {
                // Log failed request
                $auditUrl = AuditUrl::create([
                    'audit_run_id' => $run->id,
                    'url' => $url,
                    'path' => parse_url($url, PHP_URL_PATH) ?: '/',
                    'is_external' => false,
                    'source_url' => $sourceUrl,
                    'discovered_depth' => $depth,
                ]);

                AuditHttpResult::create([
                    'audit_url_id' => $auditUrl->id,
                    'status_code' => 0,
                    'response_time_ms' => 0,
                    'ttfb_ms' => 0,
                    'response_size_bytes' => 0,
                    'content_type' => null,
                    'headers' => null,
                ]);
            }
            
            // Force garbage collection to prevent memory leaks during large crawls
            if ($crawledCount % 50 === 0) {
                gc_collect_cycles();
            }
        }

        $run->update([
            'urls_crawled' => count($this->crawled),
        ]);
    }

    /**
     * Seed all registered Laravel routes (web + api) into the crawl queue.
     * This ensures routes not linked from HTML are still audited.
     */
    protected function seedRegisteredRoutes(): void
    {
        $routes = Route::getRoutes();

        foreach ($routes as $route) {
            $uri = $route->uri();
            $methods = $route->methods();

            // Only crawl GET-able routes
            if (!in_array('GET', $methods) && !in_array('HEAD', $methods)) {
                continue;
            }

            // Skip framework/package internal routes
            if (Str::startsWith($uri, ['_ignition', 'telescope', 'horizon', 'pulse', 'sanctum', 'admin/audit', 'livewire', '__clockwork', '_debugbar', 'up', 'down', 'secure-monitor'])) {
                continue;
            }

            // Skip redirect routes
            if (Str::contains($route->getActionName(), 'RedirectController')) {
                continue;
            }

            // Skip routes with required parameters (e.g. {id}, {slug})
            if (preg_match('/\{[^?]/', $uri)) {
                continue;
            }

            // Skip auth-protected routes
            $routeMiddleware = $route->middleware();
            if (in_array('auth', $routeMiddleware) || in_array('auth:sanctum', $routeMiddleware) || in_array('auth:api', $routeMiddleware)) {
                continue;
            }

            // Build full URL
            $cleanUri = preg_replace('/\{[^}]+\?\}/', '', $uri); // remove optional params
            $cleanUri = rtrim($cleanUri, '/');
            $fullUrl = $this->baseUrl . '/' . ltrim($cleanUri, '/');
            $fullUrl = $this->normalizeScheme($fullUrl);

            if (!isset($this->crawled[$fullUrl]) && !isset($this->queued[$fullUrl])) {
                $this->queue[] = [
                    'url' => $fullUrl,
                    'depth' => 1,
                    'source_url' => 'Seeded from Routes',
                ];
                $this->queued[$fullUrl] = true;
            }
        }
    }

    protected function parseRobotsTxt()
    {
        if (!config('audit.crawler.robots_txt', true)) {
            return;
        }

        try {
            $response = Http::withoutVerifying()->get($this->baseUrl . '/robots.txt');
            if ($response->successful()) {
                $lines = explode("\n", $response->body());
                $applies = false;
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (Str::startsWith(Str::lower($line), 'user-agent:')) {
                        $agent = trim(substr($line, 11));
                        $applies = ($agent === '*' || Str::contains($agent, 'Laravel-Audit-Crawler'));
                    }
                    if ($applies && Str::startsWith(Str::lower($line), 'disallow:')) {
                        $path = trim(substr($line, 9));
                        if (!empty($path)) {
                            $this->robotsExcludes[] = $path;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Ignore robots.txt issues
        }
    }

    protected function dynamicallyExcludeAuthRoutes(): void
    {
        $routes = Route::getRoutes();
        foreach ($routes as $route) {
            $routeMiddleware = $route->middleware();
            if (in_array('auth', $routeMiddleware) || in_array('auth:sanctum', $routeMiddleware) || in_array('auth:api', $routeMiddleware)) {
                $uri = $route->uri();
                $cleanUri = preg_replace('/\{[^}]+\?\}/', '', $uri); // remove optional params
                $cleanUri = preg_replace('/\{[^}]+\}/', '*', $cleanUri); // replace required params with wildcard
                $cleanUri = '/' . ltrim($cleanUri, '/');
                if (!in_array($cleanUri, $this->excludes)) {
                    $this->excludes[] = $cleanUri;
                }
            }
        }
    }

    protected function isExcluded(string $url): bool
    {
        $path = parse_url($url, PHP_URL_PATH) ?: '/';

        // Hardcode common auth paths to prevent crawler getting stuck in auth redirects
        if (Str::is(['/login*', '/logout*', '/register*', '/password*', '/email/verify*'], $path)) {
            return true;
        }
        
        // Exclude technical paths
        if (Str::is(['/_debugbar*', '/up*', '/down*', '/secure-monitor*'], $path)) {
            return true;
        }

        // Check configured and dynamically discovered excludes
        foreach ($this->excludes as $exclude) {
            if (Str::is($exclude, $path) || Str::is($exclude . '/*', $path)) {
                return true;
            }
        }

        // Check robots.txt excludes
        foreach ($this->robotsExcludes as $exclude) {
            if (Str::is($exclude, $path) || Str::is($exclude . '/*', $path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a URL points to a static asset that shouldn't be crawled.
     */
    protected function isStaticAsset(string $url): bool
    {
        $path = parse_url($url, PHP_URL_PATH) ?: '/';
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return in_array($extension, $this->skipExtensions);
    }

    /**
     * Normalize a URL's scheme to match the base URL's scheme.
     * This prevents http:// vs https:// mismatches causing duplicate crawling.
     */
    protected function normalizeScheme(string $url): string
    {
        $baseScheme = parse_url($this->baseUrl, PHP_URL_SCHEME);
        $urlScheme = parse_url($url, PHP_URL_SCHEME);

        if ($baseScheme && $urlScheme && $baseScheme !== $urlScheme) {
            $url = preg_replace('/^' . preg_quote($urlScheme, '/') . ':\/\//', $baseScheme . '://', $url);
        }

        // Strip fragments and query strings for deduplication
        $url = preg_replace('/#.*$/', '', $url);
        $url = rtrim($url, '/');

        // Ensure base URL itself keeps its trailing slash removed
        if (empty(parse_url($url, PHP_URL_PATH))) {
            $url = $url;
        }

        return $url;
    }

    protected function discoverLinks(AuditRun $run, AuditUrl $auditUrl, string $html, int $depth)
    {
        if (empty(trim($html))) {
            return;
        }

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        @$dom->loadHTML($html);
        libxml_clear_errors();

        // Collect link data before iterating (DOMNodeList is live and doesn't mix well with modifications)
        $linkData = [];
        $anchors = $dom->getElementsByTagName('a');
        for ($i = 0; $i < $anchors->length; $i++) {
            $node = $anchors->item($i);
            $href = $node->getAttribute('href');
            if (!empty($href)) {
                $linkData[] = [
                    'href' => $href,
                    'text' => trim($node->textContent) ?: null,
                ];
            }
        }

        foreach ($linkData as $ld) {
            $absoluteUrl = $this->normalizeUrl($ld['href']);
            if ($absoluteUrl) {
                $absoluteUrl = $this->normalizeScheme($absoluteUrl);

                \ThePrimeStudio\Audit\Models\AuditLink::create([
                    'audit_url_id' => $auditUrl->id,
                    'source_url' => $auditUrl->url,
                    'target_url' => $absoluteUrl,
                    'anchor_text' => $ld['text'],
                    'is_broken' => false,
                ]);

                if (Str::startsWith($absoluteUrl, $this->baseUrl) && !$this->isStaticAsset($absoluteUrl)) {
                    if (!isset($this->crawled[$absoluteUrl]) && !isset($this->queued[$absoluteUrl])) {
                        $this->queue[] = [
                            'url' => $absoluteUrl,
                            'depth' => $depth,
                            'source_url' => $auditUrl->url,
                        ];
                        $this->queued[$absoluteUrl] = true;
                    }
                }
            }
        }

        // Discover and save images
        $imageData = [];
        $images = $dom->getElementsByTagName('img');
        for ($i = 0; $i < $images->length; $i++) {
            $img = $images->item($i);
            $src = $img->getAttribute('src');
            if (!empty($src)) {
                $imageData[] = [
                    'src' => $src,
                    'alt' => $img->getAttribute('alt') ?: null,
                    'lazy' => strtolower($img->getAttribute('loading')) === 'lazy',
                ];
            }
        }

        foreach ($imageData as $imgd) {
            $absoluteSrc = $this->normalizeUrl($imgd['src']);
            if ($absoluteSrc) {
                \ThePrimeStudio\Audit\Models\AuditImage::create([
                    'audit_url_id' => $auditUrl->id,
                    'image_url' => $absoluteSrc,
                    'alt_text' => $imgd['alt'],
                    'is_lazy_loaded' => $imgd['lazy'],
                ]);
            }
        }

        // Free DOM memory
        unset($dom, $linkData, $imageData);
    }

    protected function normalizeUrl(string $url): ?string
    {
        if (Str::startsWith($url, ['mailto:', 'tel:', 'javascript:', '#'])) {
            return null;
        }

        if (Str::startsWith($url, '//')) {
            return 'http:' . $url;
        }

        if (Str::startsWith($url, '/')) {
            return $this->baseUrl . $url;
        }

        if (Str::startsWith($url, 'http://') || Str::startsWith($url, 'https://')) {
            return $url;
        }

        return null;
    }
}
