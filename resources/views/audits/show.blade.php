@extends('audit::layout')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('audit.audits.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill mb-2"><i class="fa-solid fa-arrow-left me-1"></i> Back to History</a>
        <h2 class="fw-bold mb-1">Audit Run #{{ $run->id }} Details</h2>
        <p class="text-muted mb-0">Completed at: {{ $run->completed_at ? $run->completed_at->format('Y-m-d H:i:s') : 'N/A' }}</p>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6 col-lg-3">
        <div class="card h-100 p-4 border-0 shadow-sm d-flex flex-row align-items-center gap-3">
            <div class="flex-grow-1">
                <h6 class="text-uppercase text-muted fw-bold mb-1" style="font-size: 0.75rem; letter-spacing: 0.05em;">Overall Score</h6>
                <h2 class="fw-bold mb-1 text-primary">{{ $run->overall_score ?? 'N/A' }}%</h2>
            </div>
            <div>
                <svg width="60" height="60" viewBox="0 0 36 36">
                    <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="var(--bs-border-color)" stroke-width="4"/>
                    <path stroke-dasharray="{{ $run->overall_score ?? 0 }}, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="var(--bs-primary)" stroke-width="4" stroke-linecap="round"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="card h-100 p-4 border-0 shadow-sm">
            <h6 class="text-uppercase text-muted fw-bold mb-2" style="font-size: 0.75rem; letter-spacing: 0.05em;">Security Score</h6>
            <h2 class="fw-bold mb-1 text-danger">{{ $run->security_score ?? 'N/A' }}%</h2>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="card h-100 p-4 border-0 shadow-sm">
            <h6 class="text-uppercase text-muted fw-bold mb-2" style="font-size: 0.75rem; letter-spacing: 0.05em;">Technical SEO</h6>
            <h2 class="fw-bold mb-1 text-warning">{{ $run->seo_score ?? 'N/A' }}%</h2>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="card h-100 p-4 border-0 shadow-sm">
            <h6 class="text-uppercase text-muted fw-bold mb-2" style="font-size: 0.75rem; letter-spacing: 0.05em;">Performance</h6>
            <h2 class="fw-bold mb-1 text-success">{{ $run->performance_score ?? 'N/A' }}%</h2>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0">
                <h5 class="fw-bold mb-0">Discovered URLs</h5>
                <p class="text-muted small">URLs crawled during this audit, including their discovery source.</p>
                <div class="mb-3">
                    <input type="text" id="urlSearchInput" class="form-control form-control-sm w-25" placeholder="Search URLs...">
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="urlTable">
                        <thead class="table-light text-muted" style="font-size: 0.85rem;">
                            <tr>
                                <th class="fw-semibold text-uppercase">URL</th>
                                <th class="fw-semibold text-uppercase">Source / Discovered From</th>
                                <th class="fw-semibold text-uppercase">External</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($urls as $url)
                                <tr>
                                    <td class="fw-medium text-dark text-truncate" style="max-width: 300px;" title="{{ $url->url }}"><a href="{{ $url->url }}" target="_blank">{{ $url->url }}</a></td>
                                    <td class="text-muted text-truncate" style="max-width: 300px;" title="{{ $url->source_url ?: 'N/A' }}">{{ $url->source_url ?: 'N/A' }}</td>
                                    <td>
                                        @if($url->is_external)
                                            <span class="badge bg-warning bg-opacity-10 text-warning px-2 py-1">Yes</span>
                                        @else
                                            <span class="badge bg-success bg-opacity-10 text-success px-2 py-1">No</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                @if($urls->hasPages())
                    <div class="mt-4">
                        {{ $urls->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0">
                <h5 class="fw-bold mb-0">Detected Issues</h5>
            </div>
            <div class="card-body">
                @if($run->issues->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light text-muted" style="font-size: 0.85rem;">
                                <tr>
                                    <th class="fw-semibold text-uppercase">Category</th>
                                    <th class="fw-semibold text-uppercase">Severity</th>
                                    <th class="fw-semibold text-uppercase">Issue</th>
                                    <th class="fw-semibold text-uppercase">URL</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($run->issues as $issue)
                                    <tr>
                                        <td><span class="badge bg-info bg-opacity-10 text-info px-2 py-1">{{ $issue->category }}</span></td>
                                        <td>
                                            @php
                                                $sevColor = $issue->severity === 'critical' ? 'danger' : ($issue->severity === 'high' ? 'warning' : ($issue->severity === 'medium' ? 'primary' : 'secondary'));
                                            @endphp
                                            <span class="badge bg-{{ $sevColor }} bg-opacity-10 text-{{ $sevColor }} px-2 py-1">{{ $issue->severity }}</span>
                                        </td>
                                        <td class="fw-medium text-dark">{{ $issue->title }}</td>
                                        <td class="text-muted small text-truncate" style="max-width: 250px;" title="{{ $issue->url ?: 'Global' }}">{{ $issue->url ?: 'Global' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <p class="text-muted mb-0">No issues detected in this run.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('urlSearchInput').addEventListener('keyup', function() {
        let filter = this.value.toUpperCase();
        let rows = document.querySelector("#urlTable tbody").rows;
        
        for (let i = 0; i < rows.length; i++) {
            let urlCol = rows[i].cells[0].textContent.toUpperCase();
            let sourceCol = rows[i].cells[1].textContent.toUpperCase();
            if (urlCol.indexOf(filter) > -1 || sourceCol.indexOf(filter) > -1) {
                rows[i].style.display = "";
            } else {
                rows[i].style.display = "none";
            }
        }
    });
</script>
@endpush
@endsection
