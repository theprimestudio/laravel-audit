<?php

namespace ThePrimeStudio\Audit\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use ThePrimeStudio\Audit\Models\AuditRun;

class ReportController extends Controller
{
    public function index()
    {
        $runs = AuditRun::whereNotNull('completed_at')->orderBy('id', 'desc')->take(10)->get();
        return view('audit::reports.index', compact('runs'));
    }

    public function export(Request $request)
    {
        $runId = $request->input('run_id');
        $format = $request->input('format', 'json');
        
        $run = AuditRun::with([
            'urls.httpResult', 
            'urls.seoResult', 
            'urls.securityResult',
            'urls.performanceResult',
            'issues', 
            'metrics', 
            'exceptions', 
            'routes'
        ])->findOrFail($runId);

        if ($format === 'json') {
            return response()->json($run, 200, [], JSON_PRETTY_PRINT);
        }

        if ($format === 'csv') {
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"audit-report-{$run->id}.csv\"",
            ];

            $callback = function () use ($run) {
                $file = fopen('php://output', 'w');
                
                // --- SECTION: ISSUES ---
                fputcsv($file, ['--- ISSUES ---']);
                fputcsv($file, ['Category', 'Severity', 'Title', 'Status', 'URL', 'Description']);
                foreach ($run->issues as $issue) {
                    fputcsv($file, [
                        $issue->category,
                        $issue->severity,
                        $issue->title,
                        $issue->status,
                        $issue->url,
                        $issue->description,
                    ]);
                }
                fputcsv($file, []); // Empty row for separation

                // --- SECTION: CRAWLED URLS ---
                fputcsv($file, ['--- CRAWLED URLS ---']);
                fputcsv($file, ['URL', 'Status Code', 'Response Time (ms)', 'Size (bytes)', 'Content Type']);
                foreach ($run->urls as $url) {
                    fputcsv($file, [
                        $url->url,
                        $url->httpResult?->status_code ?? 'N/A',
                        $url->httpResult?->response_time_ms ?? 'N/A',
                        $url->httpResult?->response_size_bytes ?? 'N/A',
                        $url->httpResult?->content_type ?? 'N/A',
                    ]);
                }
                fputcsv($file, []);

                // --- SECTION: SEO RESULTS ---
                fputcsv($file, ['--- SEO RESULTS ---']);
                fputcsv($file, ['URL', 'Title', 'Meta Description', 'Canonical URL', 'H1 Text']);
                foreach ($run->urls as $url) {
                    if ($url->seoResult) {
                        fputcsv($file, [
                            $url->url,
                            $url->seoResult->title,
                            $url->seoResult->meta_description,
                            $url->seoResult->canonical_url,
                            $url->seoResult->h1_text,
                        ]);
                    }
                }
                fputcsv($file, []);

                // --- SECTION: SECURITY RESULTS ---
                fputcsv($file, ['--- SECURITY RESULTS ---']);
                fputcsv($file, ['URL', 'Has HTTPS', 'Has HSTS', 'Has CSP', 'X-Frame-Options']);
                foreach ($run->urls as $url) {
                    if ($url->securityResult) {
                        fputcsv($file, [
                            $url->url,
                            $url->securityResult->has_https ? 'Yes' : 'No',
                            $url->securityResult->has_hsts ? 'Yes' : 'No',
                            $url->securityResult->has_csp ? 'Yes' : 'No',
                            $url->securityResult->x_frame_options,
                        ]);
                    }
                }
                fputcsv($file, []);

                // --- SECTION: PERFORMANCE RESULTS ---
                fputcsv($file, ['--- PERFORMANCE RESULTS ---']);
                fputcsv($file, ['URL', 'Memory Usage (MB)', 'Query Count', 'Total Query Time (ms)']);
                foreach ($run->urls as $url) {
                    if ($url->performanceResult) {
                        fputcsv($file, [
                            $url->url,
                            $url->performanceResult->memory_usage_mb,
                            $url->performanceResult->query_count,
                            $url->performanceResult->total_query_time_ms,
                        ]);
                    }
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        return abort(400, 'Unsupported export format.');
    }
}
