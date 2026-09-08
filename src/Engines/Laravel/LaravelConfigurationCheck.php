<?php

namespace ThePrimeStudio\Audit\Engines\Laravel;

use ThePrimeStudio\Audit\Contracts\AuditCheck;
use ThePrimeStudio\Audit\Contracts\AuditContext;

class LaravelConfigurationCheck implements AuditCheck
{
    public function name(): string
    {
        return 'Laravel Application Configuration Check';
    }

    public function category(): string
    {
        return 'laravel';
    }

    public function run(AuditContext $context): array
    {
        $results = [];

        // Check APP_DEBUG
        if (config('app.debug') === true) {
            $results[] = [
                'failed' => true,
                'type' => 'app_debug_enabled',
                'severity' => 'critical',
                'title' => 'Laravel Debug Mode Enabled',
                'description' => 'The application debug mode (APP_DEBUG) is enabled in configuration.',
                'recommendation' => 'Set APP_DEBUG=false in production settings to prevent sensitive data/stack trace leaks.',
            ];
        }

        // Check Session Cookie Secure flag
        if (config('session.secure') === false) {
            $results[] = [
                'failed' => true,
                'type' => 'session_cookie_insecure',
                'severity' => 'high',
                'title' => 'Insecure Session Cookies Configured',
                'description' => 'The session cookies secure flag is disabled in configuration.',
                'recommendation' => 'Set SESSION_SECURE_COOKIE=true to ensure session cookies are only transmitted over secure HTTPS connections.',
            ];
        }

        return $results;
    }
}
