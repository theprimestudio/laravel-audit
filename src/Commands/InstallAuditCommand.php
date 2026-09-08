<?php

namespace ThePrimeStudio\Audit\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Throwable;

class InstallAuditCommand extends Command
{
    protected $signature = 'audit:install
                            {--force : Overwrite published files}
                            {--no-migrate : Do not run database migrations}
                            {--no-permissions : Do not create Audit permissions}
                            {--guard=web : Guard to use for Audit permissions}';

    protected $description = 'Install and configure the Laravel Audit package';

    public function handle(): int
    {
        $this->newLine();

        $this->components->info('Installing ThePrimeStudio Laravel Audit');

        $this->publishConfig();
        $this->publishMigrations();
        $this->publishViews();

        if (! $this->option('no-migrate')) {
            $this->runMigrations();
        }

        if (! $this->option('no-permissions')) {
            $this->createPermissions();
        }

        $this->clearCaches();

        $this->newLine();
        $this->components->info('Laravel Audit installation completed successfully.');

        return self::SUCCESS;
    }

    protected function publishConfig(): void
    {
        $this->components->task('Publishing Audit configuration', function () {
            $parameters = [
                '--provider' => 'ThePrimeStudio\\Audit\\AuditServiceProvider',
                '--tag' => 'audit-config',
            ];

            if ($this->option('force')) {
                $parameters['--force'] = true;
            }

            Artisan::call('vendor:publish', $parameters);
        });
    }

    protected function publishMigrations(): void
    {
        $this->components->task('Publishing Audit migrations', function () {
            $parameters = [
                '--provider' => 'ThePrimeStudio\\Audit\\AuditServiceProvider',
                '--tag' => 'audit-migrations',
            ];

            if ($this->option('force')) {
                $parameters['--force'] = true;
            }

            Artisan::call('vendor:publish', $parameters);
        });
    }

    protected function publishViews(): void
    {
        $this->components->task('Publishing Audit views', function () {
            $parameters = [
                '--provider' => 'ThePrimeStudio\\Audit\\AuditServiceProvider',
                '--tag' => 'audit-views',
            ];

            if ($this->option('force')) {
                $parameters['--force'] = true;
            }

            Artisan::call('vendor:publish', $parameters);
        });
    }

    protected function runMigrations(): void
    {
        $this->components->task('Running database migrations', function () {
            Artisan::call('migrate', [
                '--force' => true,
            ]);
        });
    }

    protected function createPermissions(): void
    {
        if (! class_exists(Permission::class)) {
            $this->components->warn(
                'Spatie Laravel Permission is not installed. Audit permissions were skipped.'
            );

            return;
        }

        $guard = $this->option('guard');

        $permissions = config('audit.permissions', []);

        $permissionNames = array_merge(
            array_filter(
                $permissions,
                fn($value) => is_string($value) && trim($value) !== ''
            ),
            is_array($permissions['actions'] ?? null)
                ? array_filter(
                    $permissions['actions'],
                    fn($value) => is_string($value) && trim($value) !== ''
                )
                : []
        );

        $permissionNames = array_values(
            array_unique($permissionNames)
        );

        if (empty($permissionNames)) {
            $this->components->warn(
                'No Audit permissions are configured.'
            );

            return;
        }

        $created = 0;
        $existing = 0;

        foreach ($permissionNames as $permissionName) {
            try {
                $permission = Permission::findByName($permissionName, $guard);

                $existing++;

                $this->components->twoColumnDetail(
                    $permissionName,
                    '<fg=gray>exists</>'
                );
            } catch (Throwable $e) {
                Permission::create([
                    'name' => $permissionName,
                    'guard_name' => $guard,
                ]);

                $created++;

                $this->components->twoColumnDetail(
                    $permissionName,
                    '<fg=green>created</>'
                );
            }
        }

        $this->newLine();

        $this->components->info(
            "Audit permissions: {$created} created, {$existing} already existed."
        );
    }

    protected function clearCaches(): void
    {
        $this->components->task('Clearing Laravel caches', function () {
            Artisan::call('optimize:clear');
        });

        if (isset(Artisan::all()['permission:cache-reset'])) {
            $this->components->task('Clearing permission cache', function () {
                Artisan::call('permission:cache-reset');
            });
        }
    }
}
