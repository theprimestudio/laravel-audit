@extends('audit::layout')

@section('content')
<div class="d-flex justify-content-between align-items-end mb-4">
    <div>
        <h2 class="fw-bold mb-2">Dashboard Overview</h2>
        <p class="text-muted mb-0">System health and diagnostic scores for your application.</p>
    </div>
    <div>
        @can(config('audit.permissions.actions.run_audit'))
            <form action="{{ route('audit.audits.run') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary px-4 fw-semibold shadow-sm">
                    <i class="fa-solid fa-play me-2"></i> Run Diagnostics
                </button>
            </form>
        @endcan
    </div>
</div>

@php
    $activeRun = \ThePrimeStudio\Audit\Models\AuditRun::whereIn('status', ['running', 'pending'])->orderBy('id', 'desc')->first();
@endphp

@if($activeRun)
<div class="card border-primary mb-5 shadow-sm" id="active-run-card" style="border-left: 4px solid var(--primary);">
    <div class="card-body p-4 d-flex align-items-center justify-content-between gap-4">
        <div class="d-flex align-items-center gap-4 flex-grow-1">
            <i class="fa-solid fa-rotate fa-spin text-primary fs-2"></i>
            <div class="flex-grow-1">
                <h6 class="fw-bold mb-2 text-primary">Diagnostic Run #<span id="active-run-id">{{ $activeRun->id }}</span> is in progress</h6>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-medium" id="active-run-message">{{ $activeRun->progress_message ?? 'Starting...' }}</span>
                    <span class="fw-bold text-primary small" id="active-run-percent-text">{{ $activeRun->progress_percent ?? 0 }}%</span>
                </div>
                <div class="progress" style="height: 6px; border-radius: 999px;">
                    <div id="active-run-progress" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: {{ $activeRun->progress_percent ?? 0 }}%;"></div>
                </div>
            </div>
        </div>
        <a href="{{ route('audit.audits.progress') }}" class="btn btn-light border btn-sm px-3 text-nowrap fw-medium shadow-sm">
            <i class="fa-solid fa-eye me-1"></i> View Details
        </a>
    </div>
</div>

@push('scripts')
<script>
    const dashPollUrl = "{{ route('audit.audits.progress-data') }}";
    let dashPollInterval;

    function dashPoll() {
        fetch(dashPollUrl)
            .then(r => r.json())
            .then(data => {
                if (data.status === 'none') {
                    document.getElementById('active-run-card').style.display = 'none';
                    clearInterval(dashPollInterval);
                    return;
                }

                document.getElementById('active-run-percent-text').textContent = data.progress_percent + '%';
                document.getElementById('active-run-progress').style.width = data.progress_percent + '%';
                document.getElementById('active-run-message').textContent = data.progress_message;

                if (data.status === 'completed' || data.status === 'failed') {
                    clearInterval(dashPollInterval);
                    setTimeout(() => { window.location.reload(); }, 2000);
                }
            })
            .catch(() => {});
    }

    dashPollInterval = setInterval(dashPoll, 2000);
    dashPoll();
</script>
@endpush
@endif

@if($lastRun)
    <div class="row g-4 mb-5">
        <div class="col-md-6 col-lg-3">
            <div class="card h-100 p-4 border-0 d-flex flex-row align-items-center gap-3">
                <div class="flex-grow-1">
                    <h6 class="text-uppercase text-muted fw-bold mb-1" style="font-size: 0.75rem; letter-spacing: 0.05em;">
                        Overall Score
                    </h6>
                    <h2 class="fw-bold mb-1" style="color: var(--primary);">{{ $lastRun->overall_score ?? '0.00' }}%</h2>
                    <span class="badge bg-opacity-10 text-primary px-2 py-1 mt-1">Weighted average</span>
                </div>
                <div>
                    <!-- Inline SVG Score Ring -->
                    <svg width="60" height="60" viewBox="0 0 36 36">
                        <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="var(--border-color)" stroke-width="4"/>
                        <path stroke-dasharray="{{ $lastRun->overall_score ?? 0 }}, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="var(--primary)" stroke-width="4" stroke-linecap="round"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card h-100 p-4 border-0">
                <h6 class="text-uppercase text-muted fw-bold mb-2" style="font-size: 0.75rem; letter-spacing: 0.05em;">
                    Security
                </h6>
                <h2 class="fw-bold mb-1 text-danger">{{ $lastRun->security_score ?? '0.00' }}%</h2>
                <div class="mt-auto pt-2">
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar bg-danger" style="width: {{ $lastRun->security_score ?? 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card h-100 p-4 border-0">
                <h6 class="text-uppercase text-muted fw-bold mb-2" style="font-size: 0.75rem; letter-spacing: 0.05em;">
                    Technical SEO
                </h6>
                <h2 class="fw-bold mb-1 text-warning">{{ $lastRun->seo_score ?? '0.00' }}%</h2>
                <div class="mt-auto pt-2">
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar bg-warning" style="width: {{ $lastRun->seo_score ?? 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card h-100 p-4 border-0">
                <h6 class="text-uppercase text-muted fw-bold mb-2" style="font-size: 0.75rem; letter-spacing: 0.05em;">
                    Performance
                </h6>
                <h2 class="fw-bold mb-1 text-success">{{ $lastRun->performance_score ?? '0.00' }}%</h2>
                <div class="mt-auto pt-2">
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar bg-success" style="width: {{ $lastRun->performance_score ?? 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 h-100">
                <div class="card-header bg-white border-bottom pt-4 pb-3 d-flex justify-content-between align-items-center px-4">
                    <h5 class="fw-bold mb-0">Recent Issues</h5>
                    @if($lastRun->issues()->count() > 0)
                        <a href="{{ route('audit.issues.index') }}" class="btn btn-sm btn-light border px-3 fw-medium">View All</a>
                    @endif
                </div>
                <div class="card-body p-0">
                    @if($lastRun->issues()->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4">Category</th>
                                        <th>Severity</th>
                                        <th>Issue</th>
                                        <th class="pe-4">URL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lastRun->issues()->take(6)->get() as $issue)
                                        <tr>
                                            <td class="ps-4"><span class="badge bg-secondary bg-opacity-10 text-secondary border">{{ ucfirst($issue->category) }}</span></td>
                                            <td>
                                                @php
                                                    $sevColor = $issue->severity === 'critical' ? 'danger' : ($issue->severity === 'high' ? 'warning' : ($issue->severity === 'medium' ? 'primary' : 'secondary'));
                                                @endphp
                                                <span class="badge bg-{{ $sevColor }} bg-opacity-10 text-{{ $sevColor }} border border-{{ $sevColor }}">{{ ucfirst($issue->severity) }}</span>
                                            </td>
                                            <td class="fw-medium text-dark">{{ $issue->title }}</td>
                                            <td class="pe-4 text-muted small text-truncate" style="max-width: 180px;" title="{{ $issue->url ?: 'Global' }}">{{ $issue->url ?: 'Global' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="bg-success bg-opacity-10 d-inline-flex p-4 rounded-circle mb-3">
                                <i class="fa-solid fa-check text-success fs-1"></i>
                            </div>
                            <h5 class="fw-bold">No issues found!</h5>
                            <p class="text-muted mb-0">Your application is in pristine condition.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 h-100">
                <div class="card-header bg-white border-bottom pt-4 pb-3 px-4">
                    <h5 class="fw-bold mb-0">Run Details</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-3">
                            <span class="text-muted fw-medium">URLs Crawled</span>
                            <span class="fw-bold">{{ number_format($lastRun->urls_crawled) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-3">
                            <span class="text-muted fw-medium">Laravel Health</span>
                            <span class="fw-bold text-success">{{ $lastRun->laravel_score ?? '0.00' }}%</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-3">
                            <span class="text-muted fw-medium">Reliability</span>
                            <span class="fw-bold text-primary">{{ $lastRun->reliability_score ?? '0.00' }}%</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-3">
                            <span class="text-muted fw-medium">Total Issues</span>
                            <span class="fw-bold text-danger">{{ number_format($lastRun->issues_found) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-3">
                            <span class="text-muted fw-medium">Completed</span>
                            <span class="fw-semibold small">{{ $lastRun->completed_at ? $lastRun->completed_at->format('M d, Y H:i') : 'N/A' }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@else
    <div class="card border-0 text-center py-5 mt-4">
        <div class="card-body py-5">
            <div class="bg-light d-inline-flex p-4 rounded-circle mb-4">
                <i class="fa-solid fa-server text-muted fs-1"></i>
            </div>
            <h3 class="fw-bold">No Diagnostics Data</h3>
            <p class="text-muted mb-4 mx-auto" style="max-width: 400px;">Run your first website and application audit to generate insights and health scores.</p>
            @can(config('audit.permissions.actions.run_audit'))
                <form action="{{ route('audit.audits.run') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary px-5 py-2 fw-semibold shadow-sm">
                        <i class="fa-solid fa-play me-2"></i> Start Diagnostic Scan
                    </button>
                </form>
            @endcan
        </div>
    </div>
@endif
@endsection
