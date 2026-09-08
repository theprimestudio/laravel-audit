<?php

namespace ThePrimeStudio\Audit;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

class AuditServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/audit.php',
            'audit'
        );

        $this->app->singleton(Managers\CheckManager::class, function ($app) {
            return new Managers\CheckManager($app);
        });
    }

    public function boot(): void
    {
        $this->registerCommands();
        $this->registerResources();
        $this->registerRoutes();
        $this->registerGates();

        if ($this->app->bound(\Illuminate\Contracts\Http\Kernel::class)) {
            $kernel = $this->app->make(\Illuminate\Contracts\Http\Kernel::class);
            $kernel->prependMiddlewareToGroup('web', \ThePrimeStudio\Audit\Http\Middleware\AuditDiagnosticMiddleware::class);
            $kernel->prependMiddlewareToGroup('api', \ThePrimeStudio\Audit\Http\Middleware\AuditDiagnosticMiddleware::class);
        }

        $this->bootDynamicSettings();
    }

    protected function bootDynamicSettings(): void
    {
        try {
            if (\Illuminate\Support\Facades\Storage::disk('local')->exists('laravel-audit/settings.json')) {
                $settings = json_decode(\Illuminate\Support\Facades\Storage::disk('local')->get('laravel-audit/settings.json'), true);
                
                if (isset($settings['product_name'])) config(['audit.product_name' => $settings['product_name']]);
                if (isset($settings['primary_color'])) config(['audit.primary_color' => $settings['primary_color']]);
                if (isset($settings['accent_color'])) config(['audit.accent_color' => $settings['accent_color']]);
                if (isset($settings['theme_mode'])) config(['audit.theme_mode' => $settings['theme_mode']]);
                if (isset($settings['crawler_max_urls'])) config(['audit.crawler.max_urls' => $settings['crawler_max_urls']]);
                if (isset($settings['crawler_concurrency'])) config(['audit.crawler.concurrency' => $settings['crawler_concurrency']]);
                if (isset($settings['admin_panel_url'])) config(['audit.admin_panel_url' => $settings['admin_panel_url']]);
                if (isset($settings['exclude_urls'])) config(['audit.crawler.exclude_urls' => $settings['exclude_urls']]);
            }
        } catch (\Exception $e) {
            // Ignore if storage isn't ready
        }
    }

    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\InstallAuditCommand::class,
                Commands\RunAuditCommand::class,
                Commands\StatusAuditCommand::class,
                Commands\ReportAuditCommand::class,
                Commands\ClearAuditCommand::class,
            ]);
        }
    }

    protected function registerResources(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/audit.php' => config_path('audit.php'),
            ], 'audit-config');

            $this->publishes([
                __DIR__ . '/../database/migrations/' => database_path('migrations'),
            ], 'audit-migrations');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/audit'),
            ], 'audit-views');
        }

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'audit');
    }

    protected function registerRoutes(): void
    {
        $prefix = config('audit.route.prefix', 'admin/audit');
        $middleware = config('audit.route.middleware', ['web', 'auth']);

        Route::group([
            'prefix' => $prefix,
            'middleware' => $middleware,
            'namespace' => 'ThePrimeStudio\Audit\Http\Controllers',
            'as' => 'audit.',
        ], function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        });
    }

    protected function registerGates(): void
    {
        $superAdminBypass = config('audit.authorization.super_admin_bypass', true);
        $superAdminGate = config('audit.authorization.super_admin_gate', 'audit.super-admin');

        // Check if gate exists, if not register a fallback
        if (!Gate::has($superAdminGate)) {
            Gate::define($superAdminGate, function ($user) {
                return false;
            });
        }

        $permissions = config('audit.permissions', []);

        $allPermissions = array_merge(
            array_filter($permissions, fn($val) => is_string($val)),
            $permissions['actions'] ?? []
        );

        foreach ($allPermissions as $key => $permissionName) {
            if (empty($permissionName) || is_array($permissionName)) {
                continue;
            }

            Gate::define($permissionName, function ($user) use ($permissionName, $superAdminBypass, $superAdminGate) {
                if ($superAdminBypass && Gate::allows($superAdminGate)) {
                    return true;
                }

                $resolverClass = config('audit.authorization.resolver');
                if ($resolverClass && class_exists($resolverClass)) {
                    $resolver = app($resolverClass);
                    if ($resolver instanceof Contracts\PermissionResolver) {
                        return $resolver->resolve($user, $permissionName);
                    }
                }

                // Default dynamic check - e.g. check if user has a method hasPermissionTo or check an attribute
                if (method_exists($user, 'hasPermissionTo')) {
                    return $user->hasPermissionTo($permissionName);
                }

                if (method_exists($user, 'hasPermission')) {
                    return $user->hasPermission($permissionName);
                }

                // Fallback to checking properties or returning true if they have an admin attribute
                return (bool) ($user->is_admin ?? false);
            });
        }
    }
}
