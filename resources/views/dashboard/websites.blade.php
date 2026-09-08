@extends('audit::layout')

@section('content')
<div class="d-flex justify-content-between align-items-end mb-4">
    <div>
        <h2 class="fw-bold mb-2">Crawled Websites & URLs</h2>
        <p class="text-muted mb-0">All discovered internal and external URLs, response metrics, and broken links.</p>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-0 p-4">
            <h6 class="text-uppercase text-muted fw-bold mb-2" style="font-size: 0.75rem; letter-spacing: 0.05em;">Discovered Pages</h6>
            <h3 class="fw-bold mb-0">{{ $urls->where('is_external', false)->count() }}</h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 p-4">
            <h6 class="text-uppercase text-muted fw-bold mb-2" style="font-size: 0.75rem; letter-spacing: 0.05em;">External Links</h6>
            <h3 class="fw-bold mb-0">{{ $urls->where('is_external', true)->count() }}</h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 p-4">
            <h6 class="text-uppercase text-muted fw-bold mb-2" style="font-size: 0.75rem; letter-spacing: 0.05em;">Broken Links</h6>
            <h3 class="fw-bold mb-0 text-danger">
                {{ $lastRun ? \ThePrimeStudio\Audit\Models\AuditLink::whereHas('url', fn($q) => $q->where('audit_run_id', $lastRun->id))->where('is_broken', true)->count() : 0 }}
            </h3>
        </div>
    </div>
</div>

<div class="card border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="ps-4">URL</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Response Time</th>
                    <th>Size</th>
                    <th class="pe-4">Content Type</th>
                </tr>
            </thead>
            <tbody>
                @forelse($urls as $url)
                    <tr>
                        <td class="ps-4" style="max-width: 350px;">
                            <div class="text-truncate" title="{{ $url->url }}">
                                <a href="{{ $url->url }}" target="_blank" class="text-decoration-none fw-medium" style="color: var(--primary);">{{ $url->url }}</a>
                            </div>
                        </td>
                        <td>
                            @if($url->is_external)
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border">External</span>
                            @else
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary">Internal</span>
                            @endif
                        </td>
                        <td>
                            @if($url->httpResult)
                                @if($url->httpResult->status_code >= 400)
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">{{ $url->httpResult->status_code }}</span>
                                @else
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success">{{ $url->httpResult->status_code }}</span>
                                @endif
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border">Pending</span>
                            @endif
                        </td>
                        <td class="fw-medium">{{ $url->httpResult?->response_time_ms ? $url->httpResult->response_time_ms . ' ms' : 'N/A' }}</td>
                        <td>{{ $url->httpResult?->response_size_bytes ? number_format($url->httpResult->response_size_bytes / 1024, 2) . ' KB' : 'N/A' }}</td>
                        <td class="pe-4 text-muted small">{{ $url->httpResult?->content_type ?: 'N/A' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fa-solid fa-sitemap fs-3 mb-2 opacity-50"></i>
                                <p class="mb-0">No URL crawl data exists yet.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
