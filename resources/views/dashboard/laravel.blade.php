@extends('audit::layout')

@section('content')
<div class="d-flex justify-content-between align-items-end mb-4">
    <div>
        <h2 class="fw-bold mb-2">Laravel Application Diagnostics</h2>
        <p class="text-muted mb-0">Inspect routes, middleware security layers, database query profiles, and logged exceptions.</p>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card border-0 h-100">
            <div class="card-header bg-white border-bottom pt-4 pb-3 px-4">
                <h5 class="fw-bold mb-0">Registered Routes & Security</h5>
            </div>
            <div class="card-body p-0" style="max-height: 420px; overflow-y: auto;">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">URI</th>
                            <th>Method</th>
                            <th>Controller</th>
                            <th>Middleware</th>
                            <th class="pe-4">Auth</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($routes as $route)
                            <tr>
                                <td class="ps-4 fw-semibold">{{ $route->uri }}</td>
                                <td>
                                    <div class="d-flex gap-1 flex-wrap">
                                        @foreach($route->methods as $m)
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary" style="font-size: 0.65rem;">{{ $m }}</span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="text-muted small" style="max-width: 200px;">
                                    <div class="text-truncate" title="{{ $route->controller }}">{{ $route->controller }}</div>
                                </td>
                                <td class="small" style="max-width: 180px;">
                                    <div class="text-truncate text-muted" title="{{ implode(', ', $route->middleware) }}">{{ implode(', ', $route->middleware) }}</div>
                                </td>
                                <td class="pe-4">
                                    @if($route->is_protected)
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success">Protected</span>
                                    @else
                                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning">Public</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No route diagnostics loaded.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 h-100">
            <div class="card-header bg-white border-bottom pt-4 pb-3 px-4">
                <h5 class="fw-bold mb-0">Slow Queries</h5>
            </div>
            <div class="card-body p-0" style="max-height: 420px; overflow-y: auto;">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Time</th>
                            <th class="pe-4">Query</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($queries as $query)
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-bold {{ $query->time_ms > 100 ? 'text-danger' : 'text-success' }}">
                                        {{ $query->time_ms }} ms
                                    </span>
                                </td>
                                <td class="pe-4" style="max-width: 300px;">
                                    <code class="small text-dark d-block text-truncate" title="{{ $query->sql }}">{{ $query->sql }}</code>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center py-4 text-muted">No queries logged.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card border-0">
    <div class="card-header bg-white border-bottom pt-4 pb-3 px-4">
        <h5 class="fw-bold mb-0">Logged Exceptions</h5>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="ps-4">Exception Class</th>
                    <th>Message</th>
                    <th>File & Line</th>
                    <th class="pe-4">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($exceptions as $exception)
                    <tr>
                        <td class="ps-4 fw-bold text-danger">{{ $exception->class }}</td>
                        <td style="max-width: 300px;">
                            <div class="text-truncate" title="{{ $exception->message }}">{{ $exception->message }}</div>
                        </td>
                        <td class="text-muted small">{{ $exception->file }}:{{ $exception->line }}</td>
                        <td class="pe-4 fw-medium small">{{ $exception->occurred_at ? $exception->occurred_at->format('M d, Y H:i') : 'N/A' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fa-solid fa-check-circle fs-3 mb-2 text-success opacity-50"></i>
                                <p class="mb-0">No exceptions recorded in this run.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
