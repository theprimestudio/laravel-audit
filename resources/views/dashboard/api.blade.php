@extends('audit::layout')

@section('content')
<div class="d-flex justify-content-between align-items-end mb-4">
    <div>
        <h2 class="fw-bold mb-2">API Diagnostics & Performance</h2>
        <p class="text-muted mb-0">Monitor status, latency, CORS setup, and JSON structure of API endpoints.</p>
    </div>
</div>

<div class="card border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="ps-4">Endpoint</th>
                    <th>Status</th>
                    <th>Response Time</th>
                    <th>Size</th>
                    <th class="pe-4">Content Type</th>
                </tr>
            </thead>
            <tbody>
                @forelse($apiResults as $url)
                    <tr>
                        <td class="ps-4">
                            <a href="{{ $url->url }}" target="_blank" class="text-decoration-none fw-medium" style="color: var(--primary);">
                                <code>{{ $url->path }}</code>
                            </a>
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
                    @foreach($registeredApiRoutes as $route)
                        <tr>
                            <td class="ps-4">
                                <code class="fw-medium" style="color: var(--primary);">/{{ $route->uri }}</code>
                                <div class="text-muted small">Registered Route</div>
                            </td>
                            <td><span class="badge bg-secondary bg-opacity-10 text-secondary border">Not Crawled</span></td>
                            <td class="text-muted">N/A</td>
                            <td class="text-muted">N/A</td>
                            <td class="pe-4 text-muted">N/A</td>
                        </tr>
                    @endforeach
                    @if($registeredApiRoutes->isEmpty())
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fa-solid fa-server fs-3 mb-2 opacity-50"></i>
                                    <p class="mb-0">No API endpoints discovered. Ensure routes are auto-discovered or prefix-mapped.</p>
                                </div>
                            </td>
                        </tr>
                    @endif
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
