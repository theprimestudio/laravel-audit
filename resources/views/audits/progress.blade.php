@extends('audit::layout')

@section('content')
<div class="mb-4">
    <h2 class="fw-bold mb-1">Audit Progress</h2>
    <p class="text-muted mb-0">Live tracking of audit execution phases.</p>
</div>

<div class="card border-0 shadow-sm mx-auto" style="max-width: 800px;">
    <div class="card-body p-5">
        <div class="text-center mb-5">
            <div class="mb-3">
                <i class="fa-solid fa-rotate fa-spin text-primary" id="spinner-icon" style="font-size: 3rem;"></i>
            </div>
            <h3 class="fw-bold text-dark" id="progress-title">Audit Run #{{ $run->id }}</h3>
            <p class="text-muted mt-2" id="progress-status">
                Status: <span class="badge bg-primary bg-opacity-10 text-primary px-2 py-1 text-uppercase">{{ $run->status }}</span>
            </p>
        </div>

        {{-- Overall progress bar --}}
        <div class="mb-5">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="fw-bold text-dark">Overall Progress</span>
                <span id="progress-percent" class="fw-bold text-primary fs-5">{{ $run->progress_percent ?? 0 }}%</span>
            </div>
            <div class="progress" style="height: 12px; border-radius: 6px;">
                <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: {{ $run->progress_percent ?? 0 }}%; transition: width 0.5s ease;"></div>
            </div>
            <p id="progress-message" class="text-muted small mt-2 fw-medium">
                {{ $run->progress_message ?? 'Initializing...' }}
            </p>
        </div>

        {{-- Phase breakdown --}}
        <div class="d-flex flex-column gap-3">
            <div class="d-flex flex-column" id="phase-crawl">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center text-muted" style="width: 40px; height: 40px;">
                        <i class="fa-solid fa-spider"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="fw-bold mb-0">Crawling</h6>
                        <small class="text-muted" id="phase-crawl-detail">Discovering pages and URLs across the site.</small>
                    </div>
                    <div>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary" id="phase-crawl-badge">Pending</span>
                    </div>
                </div>
            </div>

            <hr class="text-muted opacity-25 my-1">

            <div class="d-flex flex-column" id="phase-audit">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center text-muted" style="width: 40px; height: 40px;">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="fw-bold mb-0">SEO, Security & Performance Audits</h6>
                        <small class="text-muted" id="phase-audit-detail">Running checks on each crawled URL.</small>
                    </div>
                    <div>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary" id="phase-audit-badge">Pending</span>
                    </div>
                </div>
            </div>

            <hr class="text-muted opacity-25 my-1">

            <div class="d-flex flex-column" id="phase-routes">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center text-muted" style="width: 40px; height: 40px;">
                        <i class="fa-solid fa-route"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="fw-bold mb-0">Route Logging</h6>
                        <small class="text-muted" id="phase-routes-detail">Cataloging all registered application routes.</small>
                    </div>
                    <div>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary" id="phase-routes-badge">Pending</span>
                    </div>
                </div>
            </div>

            <hr class="text-muted opacity-25 my-1">

            <div class="d-flex flex-column" id="phase-scoring">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center text-muted" style="width: 40px; height: 40px;">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="fw-bold mb-0">Scoring</h6>
                        <small class="text-muted" id="phase-scoring-detail">Computing final audit scores.</small>
                    </div>
                    <div>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary" id="phase-scoring-badge">Pending</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-5 text-center">
            <a href="{{ route('audit.dashboard') }}" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="fa-solid fa-arrow-left me-2"></i> Return to Dashboard
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const pollUrl = "{{ route('audit.audits.progress-data') }}";
    let pollInterval;

    function updatePhases(percent, message) {
        // Phase: Crawl (0-40%)
        const crawlBadge = document.getElementById('phase-crawl-badge');
        const crawlDetail = document.getElementById('phase-crawl-detail');
        if (percent >= 40) {
            crawlBadge.textContent = 'Complete';
            crawlBadge.className = 'badge bg-success bg-opacity-10 text-success';
        } else if (percent >= 2) {
            crawlBadge.textContent = 'Running';
            crawlBadge.className = 'badge bg-primary bg-opacity-10 text-primary';
            crawlDetail.textContent = message;
        }

        // Phase: Audit (40-80%)
        const auditBadge = document.getElementById('phase-audit-badge');
        const auditDetail = document.getElementById('phase-audit-detail');
        if (percent >= 80) {
            auditBadge.textContent = 'Complete';
            auditBadge.className = 'badge bg-success bg-opacity-10 text-success';
        } else if (percent >= 42) {
            auditBadge.textContent = 'Running';
            auditBadge.className = 'badge bg-primary bg-opacity-10 text-primary';
            auditDetail.textContent = message;
        }

        // Phase: Routes (80-90%)
        const routesBadge = document.getElementById('phase-routes-badge');
        const routesDetail = document.getElementById('phase-routes-detail');
        if (percent >= 90) {
            routesBadge.textContent = 'Complete';
            routesBadge.className = 'badge bg-success bg-opacity-10 text-success';
        } else if (percent >= 82) {
            routesBadge.textContent = 'Running';
            routesBadge.className = 'badge bg-primary bg-opacity-10 text-primary';
            routesDetail.textContent = message;
        }

        // Phase: Scoring (90-100%)
        const scoringBadge = document.getElementById('phase-scoring-badge');
        const scoringDetail = document.getElementById('phase-scoring-detail');
        if (percent >= 100) {
            scoringBadge.textContent = 'Complete';
            scoringBadge.className = 'badge bg-success bg-opacity-10 text-success';
        } else if (percent >= 92) {
            scoringBadge.textContent = 'Running';
            scoringBadge.className = 'badge bg-primary bg-opacity-10 text-primary';
            scoringDetail.textContent = message;
        }
    }

    function poll() {
        fetch(pollUrl)
            .then(r => r.json())
            .then(data => {
                if (data.status === 'none') return;

                document.getElementById('progress-percent').textContent = data.progress_percent + '%';
                document.getElementById('progress-bar').style.width = data.progress_percent + '%';
                document.getElementById('progress-message').textContent = data.progress_message;
                
                let statusColor = data.status === 'completed' ? 'success' : (data.status === 'failed' ? 'danger' : 'primary');
                document.getElementById('progress-status').innerHTML = `Status: <span class="badge bg-${statusColor} bg-opacity-10 text-${statusColor} px-2 py-1 text-uppercase">${data.status}</span>`;

                updatePhases(data.progress_percent, data.progress_message);

                if (data.status === 'completed') {
                    clearInterval(pollInterval);
                    const icon = document.getElementById('spinner-icon');
                    icon.className = 'fa-solid fa-circle-check text-success';
                    document.getElementById('progress-message').textContent = 'Audit completed successfully! Redirecting...';
                    document.getElementById('progress-message').className = 'text-success small mt-2 fw-bold';
                    document.getElementById('progress-bar').className = 'progress-bar bg-success';
                    setTimeout(() => { window.location.href = "{{ route('audit.dashboard') }}"; }, 2000);
                } else if (data.status === 'failed') {
                    clearInterval(pollInterval);
                    const icon = document.getElementById('spinner-icon');
                    icon.className = 'fa-solid fa-circle-xmark text-danger';
                    document.getElementById('progress-message').className = 'text-danger small mt-2 fw-bold';
                    document.getElementById('progress-bar').className = 'progress-bar bg-danger';
                }
            })
            .catch(() => {});
    }

    pollInterval = setInterval(poll, 2000);
    poll();
</script>
@endpush
@endsection
