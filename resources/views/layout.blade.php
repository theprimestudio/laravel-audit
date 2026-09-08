<!DOCTYPE html>
<html lang="en" data-bs-theme="{{ config('audit.theme_mode', 'light') }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('audit.product_name', 'Laravel Audit') }} - Diagnostics</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: {{ config('audit.primary_color', '#111827') }};
            --primary-hover: #374151;
            --bg-color: #f9fafb;
            --sidebar-bg: #ffffff;
            --text-main: #111827;
            --text-muted: #6b7280;
            --border-color: #e5e7eb;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            min-height: 100vh;
        }

        /* Sidebar Styling */
        .sidebar {
            width: 260px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            border-right: 1px solid var(--border-color);
            background-color: var(--sidebar-bg);
            display: flex;
            flex-direction: column;
            box-shadow: var(--shadow-sm);
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
        }

        .sidebar-header h5 {
            font-weight: 700;
            margin: 0;
            font-size: 1.125rem;
            letter-spacing: -0.025em;
        }

        .sidebar .nav-link {
            color: var(--text-muted);
            padding: 0.75rem 1.25rem;
            margin: 0.25rem 1rem;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
            font-size: 0.925rem;
            transition: all 0.2s ease;
        }

        .sidebar .nav-link:hover {
            background-color: var(--bg-color);
            color: var(--text-main);
        }

        .sidebar .nav-link.active {
            background-color: var(--primary);
            color: #ffffff;
            box-shadow: var(--shadow-sm);
        }

        .sidebar .nav-link i {
            width: 1.25rem;
            text-align: center;
            font-size: 1rem;
        }

        /* Main Content */
        .main-content {
            margin-left: 260px;
            padding: 2rem 3rem;
            min-height: 100vh;
        }

        .top-bar {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 2rem;
        }

        /* Card Styling */
        .card {
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
            transition: box-shadow 0.2s ease;
            background-color: #ffffff;
        }

        .card:hover {
            box-shadow: var(--shadow-md);
        }

        /* Typography & Utilities */
        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-weight: 600;
            letter-spacing: -0.025em;
        }

        .text-muted {
            color: var(--text-muted) !important;
        }

        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
            font-weight: 500;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
        }

        .btn-primary:hover,
        .btn-primary:focus {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            border-bottom-width: 1px;
            padding: 1rem;
            background-color: #f9fafb;
        }

        .table td {
            padding: 1rem;
            vertical-align: middle;
            color: var(--text-main);
            border-bottom: 1px solid var(--border-color);
        }

        /* Badges */
        .badge {
            font-weight: 500;
            padding: 0.35em 0.65em;
            border-radius: 9999px;
        }

        .bg-opacity-10 {
            opacity: 1 !important;
            background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
        }
    </style>
    @stack('styles')
</head>

<body>

    <aside class="sidebar">
        <div class="sidebar-header">
            <h5><i class="fa-solid fa-chart-line text-primary me-2"></i> {{ config('audit.product_name', 'Audit Pro') }}
            </h5>
        </div>
        <div class="py-3 flex-grow-1 overflow-auto">
            <ul class="nav flex-column">
                @can(config('audit.permissions.dashboard'))
                    <li class="nav-item">
                        <a href="{{ route('audit.dashboard') }}"
                            class="nav-link {{ request()->routeIs('audit.dashboard') ? 'active' : '' }}">
                            <i class="fa-solid fa-layer-group"></i> Overview
                        </a>
                    </li>
                @endcan
                @can(config('audit.permissions.websites'))
                    <li class="nav-item">
                        <a href="{{ route('audit.websites') }}"
                            class="nav-link {{ request()->routeIs('audit.websites') ? 'active' : '' }}">
                            <i class="fa-solid fa-sitemap"></i> Websites
                        </a>
                    </li>
                @endcan
                @can(config('audit.permissions.seo'))
                    <li class="nav-item">
                        <a href="{{ route('audit.seo') }}"
                            class="nav-link {{ request()->routeIs('audit.seo') ? 'active' : '' }}">
                            <i class="fa-solid fa-magnifying-glass-chart"></i> SEO
                        </a>
                    </li>
                @endcan
                @can(config('audit.permissions.security'))
                    <li class="nav-item">
                        <a href="{{ route('audit.security') }}"
                            class="nav-link {{ request()->routeIs('audit.security') ? 'active' : '' }}">
                            <i class="fa-solid fa-shield"></i> Security
                        </a>
                    </li>
                @endcan
                @can(config('audit.permissions.performance'))
                    <li class="nav-item">
                        <a href="{{ route('audit.performance') }}"
                            class="nav-link {{ request()->routeIs('audit.performance') ? 'active' : '' }}">
                            <i class="fa-solid fa-gauge-high"></i> Performance
                        </a>
                    </li>
                @endcan
                @can(config('audit.permissions.laravel'))
                    <li class="nav-item">
                        <a href="{{ route('audit.laravel') }}"
                            class="nav-link {{ request()->routeIs('audit.laravel') ? 'active' : '' }}">
                            <i class="fa-brands fa-laravel"></i> Laravel Health
                        </a>
                    </li>
                @endcan
                @can(config('audit.permissions.api'))
                    <li class="nav-item">
                        <a href="{{ route('audit.api') }}"
                            class="nav-link {{ request()->routeIs('audit.api') ? 'active' : '' }}">
                            <i class="fa-solid fa-server"></i> API Monitor
                        </a>
                    </li>
                @endcan

                <h6 class="px-4 mt-4 mb-2 text-uppercase text-muted"
                    style="font-size: 0.7rem; font-weight: 700; letter-spacing: 0.05em;">Management</h6>

                @can(config('audit.permissions.issues'))
                    <li class="nav-item">
                        <a href="{{ route('audit.issues.index') }}"
                            class="nav-link {{ request()->routeIs('audit.issues.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-triangle-exclamation"></i> Issues
                        </a>
                    </li>
                @endcan
                @can(config('audit.permissions.audits'))
                    <li class="nav-item">
                        <a href="{{ route('audit.audits.index') }}"
                            class="nav-link {{ request()->routeIs('audit.audits.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-clock-rotate-left"></i> History
                        </a>
                    </li>
                @endcan
                @can(config('audit.permissions.reports'))
                    <li class="nav-item">
                        <a href="{{ route('audit.reports.index') }}"
                            class="nav-link {{ request()->routeIs('audit.reports.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-file-contract"></i> Reports
                        </a>
                    </li>
                @endcan
                @can(config('audit.permissions.settings'))
                    <li class="nav-item">
                        <a href="{{ route('audit.settings.index') }}"
                            class="nav-link {{ request()->routeIs('audit.settings.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-sliders"></i> Settings
                        </a>
                    </li>
                @endcan
            </ul>
        </div>
        <footer class="mt-5 text-center text-muted" style="font-size: 0.85rem; padding-bottom: 1rem;">
            Developed by <a href="https://theprimestudio.com" target="_blank" class="text-decoration-none"
                style="color: var(--primary);">The Prime Studio</a>
        </footer>
    </aside>

    <main class="main-content">
        <div class="top-bar">
            <a href="{{ url(config('audit.admin_panel_url', '/admin')) }}"
                class="btn btn-light btn-sm px-3 shadow-sm border">
                <i class="fa-solid fa-arrow-left me-1"></i> Return to Admin
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                <i class="fa-solid fa-circle-exclamation me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @yield('content')
    </main>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })
        });
    </script>
    @stack('scripts')
</body>

</html>
