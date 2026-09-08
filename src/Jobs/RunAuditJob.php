<?php

namespace ThePrimeStudio\Audit\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use ThePrimeStudio\Audit\Models\AuditRun;
use ThePrimeStudio\Audit\Services\CrawlerService;
use ThePrimeStudio\Audit\Managers\CheckManager;
use ThePrimeStudio\Audit\Services\ScoringService;

class RunAuditJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1800; // 30 minutes max

    public function __construct(protected AuditRun $run) {}

    public function handle(): void
    {
        ini_set('memory_limit', '-1');

        Log::info('[Audit] Job started', ['run_id' => $this->run->id, 'memory' => memory_get_usage(true)]);

        $this->updateProgress(0, 'Initializing audit...');
        $this->run->update(['status' => 'running', 'started_at' => now()]);

        try {
            // Phase 1: Crawling (0% – 40%)
            Log::info('[Audit] Phase 1: Crawling started', ['run_id' => $this->run->id]);
            $this->updateProgress(2, 'Crawling: Starting site discovery...');
            $crawler = new CrawlerService();
            $crawler->crawl($this->run, url('/'));
            $crawledCount = $this->run->fresh()->urls_crawled;
            Log::info('[Audit] Phase 1: Crawling complete', ['run_id' => $this->run->id, 'urls_crawled' => $crawledCount, 'memory' => memory_get_usage(true)]);
            $this->updateProgress(40, "Crawling: Complete — {$crawledCount} URLs discovered");

            // Phase 2: SEO & Security Audits (40% – 80%)
            Log::info('[Audit] Phase 2: Audits started', ['run_id' => $this->run->id]);
            $this->updateProgress(42, 'Audits: Running SEO, Security & Performance checks...');
            $checkManager = app(CheckManager::class);
            $checkManager->execute($this->run);
            Log::info('[Audit] Phase 2: Audits complete', ['run_id' => $this->run->id, 'memory' => memory_get_usage(true)]);
            $this->updateProgress(80, 'Audits: All checks complete');

            // Phase 3: Route Logging (80% – 90%)
            Log::info('[Audit] Phase 3: Route logging started', ['run_id' => $this->run->id]);
            $this->updateProgress(82, 'Routes: Logging registered application routes...');
            $this->logRegisteredRoutes();
            $this->updateProgress(90, 'Routes: Complete');

            // Phase 4: Scoring (90% – 100%)
            Log::info('[Audit] Phase 4: Scoring started', ['run_id' => $this->run->id]);
            $this->updateProgress(92, 'Scoring: Computing audit scores...');
            $scoring = new ScoringService();
            $scoring->calculate($this->run);

            $this->run->update([
                'status' => 'completed',
                'progress_percent' => 100,
                'progress_message' => 'Audit completed successfully',
                'completed_at' => now(),
            ]);

            Log::info('[Audit] Job completed successfully', ['run_id' => $this->run->id, 'memory' => memory_get_usage(true)]);

        } catch (\Throwable $e) {
            Log::error('[Audit] Job failed', ['run_id' => $this->run->id, 'error' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
            $this->run->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'progress_message' => 'Failed: ' . \Illuminate\Support\Str::limit($e->getMessage(), 200),
                'completed_at' => now(),
            ]);
        }
    }

    protected function updateProgress(int $percent, string $message): void
    {
        $this->run->update([
            'progress_percent' => $percent,
            'progress_message' => $message,
        ]);
    }

    protected function logRegisteredRoutes(): void
    {
        $routes = \Illuminate\Support\Facades\Route::getRoutes();
        foreach ($routes as $route) {
            $uri = $route->uri();
            if (\Illuminate\Support\Str::startsWith($uri, ['admin/audit', '_ignition', 'telescope', 'horizon', 'pulse', 'sanctum', '_debugbar', 'up', 'down', 'secure-monitor', 'livewire', '__clockwork'])) {
                continue;
            }
            $this->run->routes()->create([
                'uri' => $uri,
                'methods' => $route->methods(),
                'name' => $route->getName(),
                'controller' => $route->getActionName(),
                'middleware' => (array) $route->middleware(),
                'is_protected' => collect($route->middleware())->contains(fn($m) => \Illuminate\Support\Str::contains($m, ['auth', 'signed', 'verified'])),
            ]);
        }
    }

    public function failed(\Throwable $exception): void
    {
        if (isset($this->run)) {
            $this->run->update([
                'status' => 'failed',
                'error_message' => 'Job Failed: ' . \Illuminate\Support\Str::limit($exception->getMessage(), 200),
                'progress_message' => 'Failed fatally.',
                'completed_at' => now(),
            ]);
        }
    }
}

