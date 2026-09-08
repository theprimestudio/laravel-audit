# Laravel Audit

A comprehensive, background-processing site crawler and auditing tool for Laravel applications. Laravel Audit dynamically discovers your pages, performs deep analysis on SEO, Security, and Performance, and monitors your application's Laravel-specific health (database queries, exceptions, routes).

Developed by [The Prime Studio](https://theprimestudio.com).

## Features

- **Comprehensive Crawler:** Background crawler that automatically discovers all pages on your site.
- **SEO Audits:** Checks for missing meta tags, canonical links, OpenGraph data, and header structures.
- **Security Audits:** Detects missing security headers (CSP, HSTS, X-Frame-Options) and mixed content.
- **Performance Profiling:** Tracks TTFB (Time to First Byte), load times, and memory usage per page.
- **Laravel Health Monitoring:** Captures all database queries and exceptions triggered during the audit.
- **Dynamic Configuration:** Update UI colors, theme modes, and exclude URLs directly from the dashboard.
- **Spatie Permissions Integration:** Automatically sets up and checks permissions if `spatie/laravel-permission` is installed.

---

## Requirements

- PHP 8.1+
- Laravel 10.0+
- A configured Queue connection (Database, Redis, etc.)

---

## Installation

1. Require the package via Composer (assuming it's published on Packagist):
```bash
composer require theprimestudio/laravel-audit
```

2. Run the installation command. This will publish the configuration, views, run the migrations, and set up permissions:
```bash
php artisan audit:install
```

> **Note:** The crawler runs via Laravel Jobs to prevent timeouts on large sites. Ensure your `.env` is NOT using `QUEUE_CONNECTION=sync`. Set it to `database` or `redis` and start a queue worker:
```bash
php artisan queue:work
```

Alternatively, you can manually publish the vendor assets using the provider:
```bash
php artisan vendor:publish --provider="ThePrimeStudio\Audit\AuditServiceProvider"
```

---

## Usage

Once installed, visit the dashboard at:
```
http://your-app.test/admin/audit
```

*(By default, the route is protected by the `web` and `auth` middlewares. You must be logged in to view it).*

### Running an Audit
1. Navigate to the **History** tab in the sidebar.
2. Click **Run New Audit**.
3. The audit will be dispatched to your queue. You can monitor the real-time progress on the Dashboard Overview.

---

## Dashboard Sections

- **Overview:** High-level summary of your latest audit scores.
- **Websites:** A list of all URLs discovered and crawled by the package.
- **SEO:** Detailed breakdown of SEO issues per page (missing titles, descriptions, H1s).
- **Security:** Security headers and vulnerability checks.
- **Performance:** Response times, page sizes, and TTFB.
- **Laravel Health:** Insight into exceptions thrown and the heaviest SQL queries executed during the crawl.
- **API Monitor:** Audit results specifically scoped to `/api` routes.
- **Settings:** Customize the product name, dashboard theme (dark/light), colors, and crawler limits (concurrency, max URLs, and excluded URLs).

---

## Configuration (`config/audit.php`)

You can modify the published configuration file at `config/audit.php`.

| Config Key | Description | Default |
|---|---|---|
| `route.prefix` | The URL prefix for the dashboard | `'admin/audit'` |
| `route.middleware` | Middlewares applied to the dashboard routes | `['web', 'auth']` |
| `database.connection` | Database connection to use for audit tables | `env('AUDIT_DB_CONNECTION', 'mysql')` |
| `admin_panel_url` | The URL for the "Return to Admin" button | `'/admin'` |
| `permissions.*` | The Spatie permission names required to view each tab | *Various* |
| `authorization.super_admin_bypass` | Whether a super admin bypasses permission checks | `true` |
| `crawler.concurrency` | Number of concurrent HTTP requests the crawler makes | `5` |
| `crawler.max_urls` | Hard limit on the number of URLs to crawl | `5000` |
| `crawler.timeout` | HTTP timeout in seconds | `60` |
| `crawler.exclude_urls` | Comma-separated list of URLs/wildcards to ignore | `''` |
| `scoring.weights` | The mathematical weight of each category for the final score | *Various* |
| `retention.audit_runs` | Days to keep historical audit run records | `90` |
| `retention.detailed_results` | Days to keep page-level audit results | `30` |

### Excluding URLs

You can exclude specific URLs or paths from being crawled by the auditor to prevent it from getting stuck or logging you out. You can configure this directly in the **Settings** tab of the dashboard, or in `config/audit.php`:

```php
'crawler' => [
    'exclude_urls' => '/admin/*, /health*, /logout',
],
```

---

## License

The Laravel Audit package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

Developed by The Prime Studio theprimestudio.com
