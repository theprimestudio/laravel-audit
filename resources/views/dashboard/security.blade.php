@extends('audit::layout')

@section('content')
<div class="d-flex justify-content-between align-items-end mb-4">
    <div>
        <h2 class="fw-bold mb-2">Security Diagnostics</h2>
        <p class="text-muted mb-0">Analyze HTTP security headers, SSL status, and sensitive configurations.</p>
    </div>
</div>

<div class="card border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="ps-4">URL Path</th>
                    <th>HTTPS</th>
                    <th>HSTS</th>
                    <th>CSP</th>
                    <th>X-Frame-Options</th>
                    <th class="pe-4">Cookie Checks</th>
                </tr>
            </thead>
            <tbody>
                @forelse($securityResults as $url)
                    @if($url->securityResult)
                        <tr>
                            <td class="ps-4" style="max-width: 200px;">
                                <div class="text-truncate" title="{{ $url->path ?: '/' }}">
                                    <a href="{{ $url->url }}" target="_blank" class="text-decoration-none fw-medium" style="color: var(--primary);">{{ $url->path ?: '/' }}</a>
                                </div>
                            </td>
                            <td>
                                @if($url->securityResult->is_https)
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success">Secure</span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">Insecure</span>
                                @endif
                            </td>
                            <td>
                                @if($url->securityResult->hsts_enabled)
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success">Enabled</span>
                                @else
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning">Disabled</span>
                                @endif
                            </td>
                            <td>
                                @if($url->securityResult->csp_header)
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success">Configured</span>
                                @else
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning">Missing</span>
                                @endif
                            </td>
                            <td>
                                @if($url->securityResult->x_frame_options)
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success">{{ $url->securityResult->x_frame_options }}</span>
                                @else
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning">Missing</span>
                                @endif
                            </td>
                            <td class="pe-4">
                                <div class="d-flex gap-2">
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success">Secure</span>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success">HttpOnly</span>
                                </div>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fa-solid fa-shield-check fs-3 mb-2 opacity-50"></i>
                                <p class="mb-0">No security audit results found.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
