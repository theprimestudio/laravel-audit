@extends('audit::layout')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Audit Runs History</h2>
            <p class="text-muted mb-0">List of all historical audits, overall scores, crawled items and status reports.</p>
        </div>
        <div>
            @can(config('audit.permissions.actions.run_audit'))
                <form action="{{ route('audit.audits.run') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm">
                        <i class="fa-solid fa-play me-2"></i> Start New Audit
                    </button>
                </form>
            @endcan
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="w-25">
            <input type="text" id="auditSearchInput" class="form-control form-control-sm" placeholder="Search audits...">
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="auditTable">
                    <thead class="table-light text-muted">
                        <tr>
                            <th class="ps-4 fw-semibold text-uppercase" style="font-size: 0.85rem;">Run ID</th>
                            <th class="fw-semibold text-uppercase" style="font-size: 0.85rem;">Status</th>
                            <th class="fw-semibold text-uppercase" style="font-size: 0.85rem;">Overall Score</th>
                            <th class="fw-semibold text-uppercase" style="font-size: 0.85rem;">Crawled URLs</th>
                            <th class="fw-semibold text-uppercase" style="font-size: 0.85rem;">Detected Issues</th>
                            <th class="fw-semibold text-uppercase" style="font-size: 0.85rem;">Duration</th>
                            <th class="pe-4 text-end fw-semibold text-uppercase" style="font-size: 0.85rem;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($runs as $run)
                            <tr>
                                <td class="ps-4 fw-bold text-dark">#{{ $run->id }}</td>
                                <td>
                                    @php
                                        $statusColor =
                                            $run->status === 'completed'
                                                ? 'success'
                                                : ($run->status === 'failed'
                                                    ? 'danger'
                                                    : ($run->status === 'running'
                                                        ? 'primary'
                                                        : 'secondary'));
                                    @endphp
                                    <span
                                        class="badge bg-{{ $statusColor }} bg-opacity-10 text-{{ $statusColor }} px-2 py-1 text-uppercase">
                                        @if ($run->status === 'running')
                                            <i class="fa-solid fa-circle-notch fa-spin me-1"></i>
                                        @endif
                                        {{ $run->status }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-bold text-primary fs-5">
                                        {{ $run->overall_score ? $run->overall_score . '%' : 'N/A' }}
                                    </span>
                                </td>
                                <td class="text-muted">{{ $run->urls_crawled }} URLs</td>
                                <td>
                                    <span
                                        class="badge bg-{{ $run->issues_found > 0 ? 'warning' : 'success' }} bg-opacity-10 text-{{ $run->issues_found > 0 ? 'warning' : 'success' }} px-2 py-1">
                                        {{ $run->issues_found }} issues
                                    </span>
                                </td>
                                <td class="text-muted small">
                                    {{ $run->completed_at ? $run->completed_at->diffInSeconds($run->started_at) . ' sec' : 'N/A' }}
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('audit.audits.show', $run->id) }}"
                                            class="btn btn-sm btn-outline-info rounded-pill px-3" data-bs-toggle="tooltip"
                                            title="View detailed history for this run.">
                                            <i class="fa-solid fa-list me-1"></i> Details
                                        </a>
                                        @if (in_array($run->status, ['running', 'pending']))
                                            <a href="{{ route('audit.audits.progress') }}"
                                                class="btn btn-sm btn-outline-primary rounded-pill px-3"
                                                data-bs-toggle="tooltip" title="View live progress of this audit.">
                                                <i class="fa-solid fa-eye me-1"></i> Progress
                                            </a>
                                        @endif
                                        <form action="{{ route('audit.audits.delete', $run) }}" method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete this audit run history?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                                data-bs-toggle="tooltip"
                                                title="Permanently delete this audit and its data.">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-folder-open fs-2 mb-3 opacity-50"></i>
                                    <h5>No audits have been executed yet.</h5>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($runs->hasPages())
            <div class="card-footer bg-transparent border-0 pt-4 pb-4 px-4">
                {{ $runs->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            document.getElementById('auditSearchInput').addEventListener('keyup', function() {
                let filter = this.value.toUpperCase();
                let rows = document.querySelector("#auditTable tbody").rows;

                for (let i = 0; i < rows.length; i++) {
                    let td = rows[i].cells[0]; // Search by Run ID mainly
                    let tdStatus = rows[i].cells[1]; // Search by Status
                    if (td && tdStatus) {
                        let txtValue = td.textContent || td.innerText;
                        let txtStatus = tdStatus.textContent || tdStatus.innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1 || txtStatus.toUpperCase().indexOf(filter) > -
                            1) {
                            rows[i].style.display = "";
                        } else {
                            rows[i].style.display = "none";
                        }
                    }
                }
            });
        </script>
    @endpush
@endsection
