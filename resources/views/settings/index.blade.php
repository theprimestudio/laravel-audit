@extends('audit::layout')

@section('content')
<div class="d-flex justify-content-between align-items-end mb-4">
    <div>
        <h2 class="fw-bold mb-2">Settings</h2>
        <p class="text-muted mb-0">Customize UI, themes, crawl limits, and audit thresholds.</p>
    </div>
</div>

<div class="card border-0" style="max-width: 720px;">
    <div class="card-body p-4">
        <form action="{{ route('audit.settings.update') }}" method="POST">
            @csrf
            
            <div class="mb-4">
                <label class="form-label fw-semibold small text-uppercase text-muted">Product / Platform Name</label>
                <input type="text" name="product_name" value="{{ $settings['product_name'] ?? 'Laravel Audit' }}" class="form-control">
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold small text-uppercase text-muted">Admin Panel URL</label>
                <input type="text" name="admin_panel_url" value="{{ $settings['admin_panel_url'] ?? config('audit.admin_panel_url', '/admin') }}" class="form-control">
                <div class="form-text">Used for the "Return to Admin" button.</div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold small text-uppercase text-muted">Primary Theme Color</label>
                    <input type="color" name="primary_color" value="{{ $settings['primary_color'] ?? '#111827' }}" class="form-control form-control-color w-100" style="height: 42px;">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small text-uppercase text-muted">Accent Color</label>
                    <input type="color" name="accent_color" value="{{ $settings['accent_color'] ?? '#374151' }}" class="form-control form-control-color w-100" style="height: 42px;">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold small text-uppercase text-muted">Theme Mode</label>
                <select name="theme_mode" class="form-select">
                    <option value="light" {{ ($settings['theme_mode'] ?? 'light') === 'light' ? 'selected' : '' }}>Light Theme</option>
                    <option value="dark" {{ ($settings['theme_mode'] ?? 'light') === 'dark' ? 'selected' : '' }}>Dark Theme</option>
                </select>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold small text-uppercase text-muted">Crawl Concurrency</label>
                    <input type="number" name="crawler_concurrency" value="{{ $settings['crawler_concurrency'] ?? 3 }}" class="form-control" min="1" max="10">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small text-uppercase text-muted">Max URLs to Crawl</label>
                    <input type="number" name="crawler_max_urls" value="{{ $settings['crawler_max_urls'] ?? 100 }}" class="form-control" min="10" max="5000">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold small text-uppercase text-muted">Exclude URLs / Paths</label>
                <textarea name="exclude_urls" class="form-control" rows="3" placeholder="/admin*, *dashboard*, etc.">{{ $settings['exclude_urls'] ?? '' }}</textarea>
                <div class="form-text">Comma-separated list of URLs or paths to skip during crawl.</div>
            </div>

            <button type="submit" class="btn btn-primary px-4 fw-semibold">
                <i class="fa-solid fa-floppy-disk me-2"></i> Save Settings
            </button>
        </form>
    </div>
</div>
@endsection
