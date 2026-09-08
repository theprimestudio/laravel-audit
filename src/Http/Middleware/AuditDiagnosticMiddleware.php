<?php

namespace ThePrimeStudio\Audit\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Log\Events\MessageLogged;
use ThePrimeStudio\Audit\Models\AuditUrl;
use ThePrimeStudio\Audit\Models\AuditPerformanceResult;
use ThePrimeStudio\Audit\Models\AuditQuery;
use ThePrimeStudio\Audit\Models\AuditException;

class AuditDiagnosticMiddleware
{
    protected static float $startTime = 0;
    protected static int $startMemory = 0;
    protected static ?int $runId = null;
    protected static bool $isAuditRequest = false;

    public function handle(Request $request, Closure $next)
    {
        // Only activate for crawler requests identified by our custom header
        // Normal browser requests must NEVER be intercepted
        $runId = $request->header('X-Audit-Run-Id');
        $userAgent = $request->header('User-Agent', '');

        if ($runId && str_contains($userAgent, 'Laravel-Audit-Crawler')) {
            self::$isAuditRequest = true;
            self::$runId = (int) $runId;
            self::$startTime = microtime(true);
            self::$startMemory = memory_get_usage();

            DB::enableQueryLog();

            // Listen for logged exceptions
            Event::listen(MessageLogged::class, function (MessageLogged $event) {
                if (isset($event->context['exception']) && $event->context['exception'] instanceof \Throwable) {
                    app()->instance('audit.exception', $event->context['exception']);
                }
            });
        }

        return $next($request);
    }

    public function terminate(Request $request, $response)
    {
        if (!self::$isAuditRequest || !self::$runId) {
            // Reset static state for safety
            self::reset();
            return;
        }

        try {
            // Find the audit url record for this path and run
            $auditUrl = AuditUrl::where('audit_run_id', self::$runId)
                ->where(function ($q) use ($request) {
                    $q->where('url', $request->fullUrl())
                      ->orWhere('path', $request->getPathInfo());
                })
                ->first();

            if (!$auditUrl) {
                return;
            }

            $queries = DB::getQueryLog();
            $queryCount = count($queries);
            $queryTimeMs = (int) array_sum(array_column($queries, 'time'));

            // Save queries (limit to first 100 per URL to avoid memory issues)
            $queriesToSave = array_slice($queries, 0, 100);
            foreach ($queriesToSave as $q) {
                AuditQuery::create([
                    'audit_run_id' => self::$runId,
                    'sql' => $q['query'],
                    'bindings' => $q['bindings'],
                    'time_ms' => (int) $q['time'],
                    'connection' => DB::getDefaultConnection(),
                ]);
            }

            // Save performance result
            $memoryUsageBytes = max(0, memory_get_peak_usage() - self::$startMemory);
            AuditPerformanceResult::create([
                'audit_url_id' => $auditUrl->id,
                'memory_usage_bytes' => $memoryUsageBytes,
                'query_count' => $queryCount,
                'query_time_ms' => $queryTimeMs,
            ]);

            // Save exception if any was logged
            if (app()->bound('audit.exception')) {
                $exception = app('audit.exception');
                if ($exception instanceof \Throwable) {
                    AuditException::create([
                        'audit_run_id' => self::$runId,
                        'class' => get_class($exception),
                        'message' => $exception->getMessage(),
                        'trace' => array_slice($exception->getTrace(), 0, 10),
                        'file' => $exception->getFile(),
                        'line' => $exception->getLine(),
                        'occurred_at' => now(),
                    ]);
                }
            }
        } finally {
            // Always reset static state and disable query log
            DB::disableQueryLog();
            DB::flushQueryLog();
            self::reset();
        }
    }

    /**
     * Reset all static state to prevent leaking between requests.
     */
    protected static function reset(): void
    {
        self::$runId = null;
        self::$startTime = 0;
        self::$startMemory = 0;
        self::$isAuditRequest = false;
    }
}
