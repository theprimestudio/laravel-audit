<?php

namespace ThePrimeStudio\Audit\Commands;

use Illuminate\Console\Command;
use ThePrimeStudio\Audit\Models\AuditRun;

class StatusAuditCommand extends Command
{
    protected $signature = 'audit:status';
    protected $description = 'Check status of the last audit run';

    public function handle(): int
    {
        $lastRun = AuditRun::orderBy('id', 'desc')->first();

        if (!$lastRun) {
            $this->warn('No audits have been executed yet.');
            return 0;
        }

        $this->table(
            ['Parameter', 'Value'],
            [
                ['Run ID', "#{$lastRun->id}"],
                ['Status', $lastRun->status],
                ['Overall Score', $lastRun->overall_score ? "{$lastRun->overall_score}%" : 'N/A'],
                ['SEO Score', $lastRun->seo_score ? "{$lastRun->seo_score}%" : 'N/A'],
                ['Security Score', $lastRun->security_score ? "{$lastRun->security_score}%" : 'N/A'],
                ['Performance Score', $lastRun->performance_score ? "{$lastRun->performance_score}%" : 'N/A'],
                ['Laravel Score', $lastRun->laravel_score ? "{$lastRun->laravel_score}%" : 'N/A'],
                ['URLs Crawled', $lastRun->urls_crawled],
                ['Issues Detected', $lastRun->issues_found],
                ['Started At', $lastRun->started_at],
                ['Completed At', $lastRun->completed_at ?? 'N/A'],
            ]
        );

        return 0;
    }
}
