@extends('audit::layout')

@section('content')
<div class="d-flex justify-content-between align-items-end mb-4">
    <div>
        <h2 class="fw-bold mb-2">Performance Profiling</h2>
        <p class="text-muted mb-0">Measure page speed, TTFB, database query count, and memory overhead.</p>
    </div>
</div>

<div class="card border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="ps-4">URL Path</th>
                    <th>Response Time</th>
                    <th>Database Queries</th>
                    <th class="pe-4">Memory Usage</th>
                </tr>
            </thead>
            <tbody>
                @forelse($performanceResults as $url)
                    @if($url->performanceResult)
                        <tr>
                            <td class="ps-4" style="max-width: 250px;">
                                <div class="text-truncate" title="{{ $url->path ?: '/' }}">
                                    <a href="{{ $url->url }}" target="_blank" class="text-decoration-none fw-medium" style="color: var(--primary);">{{ $url->path ?: '/' }}</a>
                                </div>
                            </td>
                            <td>
                                <span class="fw-bold {{ ($url->httpResult?->response_time_ms ?? 0) > 800 ? 'text-danger' : 'text-success' }}">
                                    {{ $url->httpResult?->response_time_ms ?: 'N/A' }} ms
                                </span>
                            </td>
                            <td>
                                @if($url->performanceResult->query_count > 15)
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">
                                        {{ $url->performanceResult->query_count }} queries ({{ $url->performanceResult->query_time_ms }} ms)
                                    </span>
                                @else
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success">
                                        {{ $url->performanceResult->query_count }} queries ({{ $url->performanceResult->query_time_ms }} ms)
                                    </span>
                                @endif
                            </td>
                            <td class="pe-4 fw-medium">
                                {{ number_format($url->performanceResult->memory_usage_bytes / 1024 / 1024, 2) }} MB
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fa-solid fa-gauge-high fs-3 mb-2 opacity-50"></i>
                                <p class="mb-0">No performance audit results found.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
