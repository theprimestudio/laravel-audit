<?php

namespace ThePrimeStudio\Audit\Commands;

use Illuminate\Console\Command;
use ThePrimeStudio\Audit\Models\AuditRun;

class ReportAuditCommand extends Command
{
    protected $signature = 'audit:report {run_id? : The ID of the run to report}';
    protected $description = 'Generate a detailed console diagnostic report';

    public function handle(): int
    {
        $runId = $this->argument('run_id');
        $run = $runId ? AuditRun::find($runId) : AuditRun::orderBy('id', 'desc')->first();

        if (!$run) {
            $this->error('Audit run not found.');
            return 1;
        }

        $this->info("=== Diagnostic Report for Run #{$run->id} ===");
        $this->info("Status: " . strtoupper($run->status));
        $this->info("Overall Score: " . ($run->overall_score ?? '0') . "%");
        $this->info("---------------------------------------------");

        $issues = $run->issues()->orderBy('severity', 'asc')->get();

        if ($issues->isEmpty()) {
            $this->info("No issues detected! 🎉");
        } else {
            $this->table(
                ['Severity', 'Category', 'Issue Title', 'URL'],
                $issues->map(fn($issue) => [
                    strtoupper($issue->severity),
                    strtoupper($issue->category),
                    $issue->title,
                    $issue->url ?: 'Global',
                ])->toArray()
            );
        }

        return 0;
    }
}
