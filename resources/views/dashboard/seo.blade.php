@extends('audit::layout')

@section('content')
<div class="d-flex justify-content-between align-items-end mb-4">
    <div>
        <h2 class="fw-bold mb-2">Technical SEO Diagnostics</h2>
        <p class="text-muted mb-0">Inspect search engine metadata, headings, schemas, and missing attributes.</p>
    </div>
</div>

<div class="card border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="ps-4">URL Path</th>
                    <th>Title</th>
                    <th>Meta Description</th>
                    <th>Canonical</th>
                    <th>H1 Headings</th>
                    <th class="pe-4">Tags</th>
                </tr>
            </thead>
            <tbody>
                @forelse($seoResults as $url)
                    @if($url->seoResult)
                        <tr>
                            <td class="ps-4" style="max-width: 200px;">
                                <div class="text-truncate" title="{{ $url->path ?: '/' }}">
                                    <a href="{{ $url->url }}" target="_blank" class="text-decoration-none fw-medium" style="color: var(--primary);">{{ $url->path ?: '/' }}</a>
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium text-dark text-truncate" style="max-width: 200px;" title="{{ $url->seoResult->title }}">
                                    {!! $url->seoResult->title ? e($url->seoResult->title) : '<span class="text-danger"><i class="fa-solid fa-circle-exclamation me-1"></i> Missing</span>' !!}
                                </div>
                                <div class="text-muted small">
                                    {{ strlen($url->seoResult->title ?? '') }} chars
                                </div>
                            </td>
                            <td>
                                <div class="text-dark text-truncate" style="max-width: 250px;" title="{{ $url->seoResult->meta_description }}">
                                    {!! $url->seoResult->meta_description ? e($url->seoResult->meta_description) : '<span class="text-danger"><i class="fa-solid fa-circle-exclamation me-1"></i> Missing</span>' !!}
                                </div>
                                <div class="text-muted small">
                                    {{ strlen($url->seoResult->meta_description ?? '') }} chars
                                </div>
                            </td>
                            <td>
                                @if($url->seoResult->canonical_url)
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success"><i class="fa-solid fa-check me-1"></i> Valid</span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger"><i class="fa-solid fa-xmark me-1"></i> Missing</span>
                                @endif
                            </td>
                            <td>
                                @if($url->seoResult->h1_headings && count($url->seoResult->h1_headings) > 0)
                                    <div class="fw-medium text-dark">
                                        {{ count($url->seoResult->h1_headings) }} H1s
                                    </div>
                                    <div class="text-muted small text-truncate" style="max-width: 150px;" title="{{ implode(', ', $url->seoResult->h1_headings) }}">
                                        {{ implode(', ', array_slice($url->seoResult->h1_headings, 0, 2)) }}
                                    </div>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger"><i class="fa-solid fa-xmark me-1"></i> None</span>
                                @endif
                            </td>
                            <td class="pe-4">
                                <div class="d-flex gap-2">
                                    <span class="badge {{ $url->seoResult->has_open_graph ? 'bg-success bg-opacity-10 text-success border border-success' : 'bg-danger bg-opacity-10 text-danger border border-danger' }}" title="Open Graph">OG</span>
                                    <span class="badge {{ $url->seoResult->has_twitter_card ? 'bg-success bg-opacity-10 text-success border border-success' : 'bg-danger bg-opacity-10 text-danger border border-danger' }}" title="Twitter Card">TW</span>
                                </div>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fa-solid fa-magnifying-glass-chart fs-3 mb-2 opacity-50"></i>
                                <p class="mb-0">No SEO audit results found.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
