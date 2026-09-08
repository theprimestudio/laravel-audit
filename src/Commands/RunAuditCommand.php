<?php

namespace ThePrimeStudio\Audit\Commands;

use Illuminate\Console\Command;
use ThePrimeStudio\Audit\Models\AuditRun;
use ThePrimeStudio\Audit\Jobs\RunAuditJob;

class RunAuditCommand extends Command
{
    protected $signature = 'audit:run {--sync : Run the audit synchronously}';
    protected $description = 'Trigger a new diagnostic website and application audit';

    public function handle(): int
    {
        $this->info('Starting Laravel Audit Diagnostic run...');

        $run = AuditRun::create([
            'status' => 'pending',
            'started_at' => now(),
        ]);

        if ($this->option('sync') || config('queue.default') === 'sync') {
            $this->info('Executing synchronously...');
            $job = new RunAuditJob($run);
            $job->handle();
            $run->refresh();
            if ($run->status === 'completed') {
                $this->info("Audit completed successfully! Score: {$run->overall_score}%");
            } else {
                $this->error("Audit failed: {$run->error_message}");
            }
        } else {
            dispatch(new RunAuditJob($run));
            $this->info("Audit job dispatched to queue. Run ID: #{$run->id}");
        }

        return 0;
    }
}
