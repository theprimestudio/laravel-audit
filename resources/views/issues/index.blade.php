@extends('audit::layout')

@section('content')
<div class="d-flex justify-content-between align-items-end mb-4">
    <div>
        <h2 class="fw-bold mb-2">Diagnostic Issues</h2>
        <p class="text-muted mb-0">All discovered problems, security risks, SEO errors, and optimization items.</p>
    </div>
</div>

<div class="card border-0 mb-4">
    <div class="card-body p-4">
        <form action="{{ route('audit.issues.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search title or URL..." class="form-control form-control-sm">
            </div>
            
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Category</label>
                <select name="category" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All</option>
                    <option value="security" {{ request('category') === 'security' ? 'selected' : '' }}>Security</option>
                    <option value="seo" {{ request('category') === 'seo' ? 'selected' : '' }}>SEO</option>
                    <option value="performance" {{ request('category') === 'performance' ? 'selected' : '' }}>Performance</option>
                    <option value="reliability" {{ request('category') === 'reliability' ? 'selected' : '' }}>Reliability</option>
                    <option value="laravel" {{ request('category') === 'laravel' ? 'selected' : '' }}>Laravel</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Severity</label>
                <select name="severity" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All</option>
                    <option value="critical" {{ request('severity') === 'critical' ? 'selected' : '' }}>Critical</option>
                    <option value="high" {{ request('severity') === 'high' ? 'selected' : '' }}>High</option>
                    <option value="medium" {{ request('severity') === 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="low" {{ request('severity') === 'low' ? 'selected' : '' }}>Low</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Status</label>
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open / Active</option>
                    <option value="acknowledged" {{ request('status') === 'acknowledged' ? 'selected' : '' }}>Acknowledged</option>
                    <option value="ignored" {{ request('status') === 'ignored' ? 'selected' : '' }}>Ignored</option>
                    <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                </select>
            </div>

            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Category</th>
                        <th>Severity</th>
                        <th>Issue</th>
                        <th>URL</th>
                        <th class="pe-4 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($issues as $issue)
                        <tr>
                            <td class="ps-4"><span class="badge bg-secondary bg-opacity-10 text-secondary border">{{ ucfirst($issue->category) }}</span></td>
                            <td>
                                @php
                                    $sevColor = $issue->severity === 'critical' ? 'danger' : ($issue->severity === 'high' ? 'warning' : ($issue->severity === 'medium' ? 'primary' : 'secondary'));
                                @endphp
                                <span class="badge bg-{{ $sevColor }} bg-opacity-10 text-{{ $sevColor }} border border-{{ $sevColor }}">{{ ucfirst($issue->severity) }}</span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $issue->title }}</div>
                                <div class="text-muted small mt-1">{{ $issue->description }}</div>
                                @if($issue->recommendation)
                                    <div class="text-success small mt-1 fw-medium"><i class="fa-solid fa-lightbulb me-1"></i> {{ $issue->recommendation }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="text-muted small text-truncate" style="max-width: 200px;" title="{{ $issue->url ?: 'Global' }}">{{ $issue->url ?: 'Global' }}</div>
                            </td>
                            <td class="pe-4 text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    @if($issue->status === 'open')
                                        <form action="{{ route('audit.issues.resolve', $issue) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-light border" title="Resolve"><i class="fa-solid fa-check text-success"></i></button>
                                        </form>
                                        <form action="{{ route('audit.issues.ignore', $issue) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-light border" title="Ignore"><i class="fa-solid fa-eye-slash text-muted"></i></button>
                                        </form>
                                    @endif
                                    <form action="{{ route('audit.issues.delete', $issue) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light border" title="Delete"><i class="fa-solid fa-trash text-danger"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fa-solid fa-clipboard-check fs-3 mb-2 opacity-50"></i>
                                    <p class="fw-bold mb-0">No matching issues found.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if($issues->hasPages())
        <div class="card-footer bg-white border-top px-4 py-3">
            {{ $issues->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
