<?php

namespace ThePrimeStudio\Audit\Engines\Security;

use ThePrimeStudio\Audit\Contracts\AuditCheck;
use ThePrimeStudio\Audit\Contracts\AuditContext;

class HeadersCheck implements AuditCheck
{
    public function name(): string
    {
        return 'HTTP Security Headers Check';
    }

    public function category(): string
    {
        return 'security';
    }

    public function run(AuditContext $context): array
    {
        $response = $context->getResponse();
        if (!$response) {
            return [];
        }

        $results = [];

        // Check Content-Security-Policy
        if (!$response->header('Content-Security-Policy')) {
            $results[] = [
                'failed' => true,
                'type' => 'missing_csp',
                'severity' => 'high',
                'title' => 'Missing Content-Security-Policy Header',
                'description' => 'The Content-Security-Policy (CSP) header is not configured.',
                'recommendation' => 'Configure CSP headers to restrict allowed resource loading origins.',
            ];
        }

        // Check HSTS
        if (str_starts_with($context->getUrl(), 'https://') && !$response->header('Strict-Transport-Security')) {
            $results[] = [
                'failed' => true,
                'type' => 'missing_hsts',
                'severity' => 'medium',
                'title' => 'Strict-Transport-Security Header Missing',
                'description' => 'HTTP Strict Transport Security (HSTS) is not enabled on this HTTPS route.',
                'recommendation' => 'Add Strict-Transport-Security header (e.g. max-age=31536000).',
            ];
        }

        // Check X-Frame-Options
        if (!$response->header('X-Frame-Options')) {
            $results[] = [
                'failed' => true,
                'type' => 'missing_x_frame_options',
                'severity' => 'medium',
                'title' => 'X-Frame-Options Header Missing',
                'description' => 'The X-Frame-Options header is missing, leaving the page vulnerable to clickjacking.',
                'recommendation' => 'Set X-Frame-Options header to DENY or SAMEORIGIN.',
            ];
        }

        // Check X-Content-Type-Options
        if (strtolower($response->header('X-Content-Type-Options') ?? '') !== 'nosniff') {
            $results[] = [
                'failed' => true,
                'type' => 'missing_x_content_type_options',
                'severity' => 'low',
                'title' => 'X-Content-Type-Options Header Missing or Invalid',
                'description' => 'The X-Content-Type-Options header is not set to "nosniff".',
                'recommendation' => 'Configure X-Content-Type-Options header with value "nosniff".',
            ];
        }

        return $results;
    }
}
