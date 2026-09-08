@extends('audit::layout')

@section('content')
<div class="d-flex justify-content-between align-items-end mb-4">
    <div>
        <h2 class="fw-bold mb-2">Reports</h2>
        <p class="text-muted mb-0">Export diagnostic summaries, raw CSV data, or JSON datasets from completed audits.</p>
    </div>
</div>

<div class="card border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="ps-4">Audit Date</th>
                    <th>Overall Score</th>
                    <th>Issues Found</th>
                    <th class="pe-4">Export</th>
                </tr>
            </thead>
            <tbody>
                @forelse($runs as $run)
                    <tr>
                        <td class="ps-4 fw-medium">{{ $run->completed_at ? $run->completed_at->format('M d, Y H:i') : 'Incomplete' }}</td>
                        <td>
                            <span class="fw-bold" style="color: var(--primary);">{{ $run->overall_score ? $run->overall_score . '%' : 'N/A' }}</span>
                        </td>
                        <td>
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">{{ $run->issues_found }} issues</span>
                        </td>
                        <td class="pe-4">
                            <div class="d-flex gap-2">
                                <a href="{{ route('audit.reports.export', ['run_id' => $run->id, 'format' => 'json']) }}" class="btn btn-sm btn-light border fw-medium px-3">
                                    <i class="fa-solid fa-code me-1"></i> JSON
                                </a>
                                <a href="{{ route('audit.reports.export', ['run_id' => $run->id, 'format' => 'csv']) }}" class="btn btn-sm btn-light border fw-medium px-3">
                                    <i class="fa-solid fa-file-csv me-1"></i> CSV
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fa-solid fa-file-contract fs-3 mb-2 opacity-50"></i>
                                <p class="mb-0">No completed audit reports found.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
