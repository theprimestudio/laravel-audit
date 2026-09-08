<?php

return [
    'route' => [
        'prefix' => 'admin/audit',
        'middleware' => ['web', 'auth'],
    ],

    'database' => [
        'connection' => env('AUDIT_DB_CONNECTION', 'mysql'),
    ],
    
    'admin_panel_url' => '/admin',

    'permissions' => [
        'dashboard' => 'admin_audit_view',
        'websites' => 'admin_audit_websites_view',
        'seo' => 'admin_audit_seo_view',
        'security' => 'admin_audit_security_view',
        'performance' => 'admin_audit_performance_view',
        'laravel' => 'admin_audit_laravel_view',
        'api' => 'admin_audit_api_view',
        'issues' => 'admin_audit_issues_view',
        'audits' => 'admin_audit_audits_view',
        'reports' => 'admin_audit_reports_view',
        'settings' => 'admin_audit_settings_view',

        'actions' => [
            'run_audit' => 'admin_audit_run',
            'resolve_issue' => 'admin_audit_issues_resolve',
            'ignore_issue' => 'admin_audit_issues_ignore',
            'delete_issue' => 'admin_audit_issues_delete',
            'delete_audit' => 'admin_audit_audits_delete',
            'export_report' => 'admin_audit_reports_export',
            'manage_settings' => 'admin_audit_settings_manage',
        ],
    ],

    'authorization' => [
        'resolver' => null,
        'super_admin_bypass' => true,
        'super_admin_gate' => 'admin_audit_super_admin',
    ],

    'crawler' => [
        'concurrency' => 5,
        'max_urls' => 5000,
        'timeout' => 60,
        'retry' => 3,

        'exclude_paths' => [
            '/admin/*',
            '/login',
            '/logout',
            '/password/*',
        ],

        'robots_txt' => true,
    ],

    'scoring' => [
        'weights' => [
            'security' => 0.30,
            'seo' => 0.25,
            'performance' => 0.20,
            'reliability' => 0.15,
            'laravel' => 0.10,
        ],
    ],

    'retention' => [
        'audit_runs' => 90,
        'detailed_results' => 30,
    ],
];
