<?php

namespace ThePrimeStudio\Audit\Commands;

use Illuminate\Console\Command;
use ThePrimeStudio\Audit\Models\AuditRun;

class ClearAuditCommand extends Command
{
    protected $signature = 'audit:clear {--force : Force deletion without confirmation}';
    protected $description = 'Clear all historical audit runs and logs';

    public function handle(): int
    {
        if (!$this->option('force') && !$this->confirm('Are you sure you want to delete all historical audit logs?')) {
            $this->info('Operation cancelled.');
            return 0;
        }

        AuditRun::truncate();
        $this->info('Audit history database tables cleared successfully.');

        return 0;
    }
}
