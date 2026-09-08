@extends('audit::layout')

@section('content')
<div class="mb-4">
    <h2 class="fw-bold mb-1">Audit Already in Progress</h2>
    <p class="text-muted mb-0">An audit is currently running. Please choose how you would like to proceed.</p>
</div>

<div class="card border-0 shadow-sm mx-auto mt-5" style="max-width: 650px;">
    <div class="card-body p-5 text-center">
        <div class="mb-4">
            <i class="fa-solid fa-person-running text-primary" style="font-size: 4rem;"></i>
        </div>
        
        <h3 class="fw-bold mb-3">Audit Run #{{ $stuckRun->id }} is currently {{ $stuckRun->status }}</h3>
        
        <p class="text-muted mb-4">
            Started: <strong>{{ $stuckRun->started_at ? $stuckRun->started_at->format('Y-m-d H:i') : 'Unknown' }}</strong><br>
            @if($stuckRun->progress_message)
                Current Status: <strong>{{ $stuckRun->progress_message }} ({{ $stuckRun->progress_percent ?? 0 }}%)</strong>
            @endif
        </p>

        <div class="d-flex flex-column gap-3">
            <a href="{{ route('audit.audits.progress') }}" class="btn btn-primary rounded-pill py-3 fw-bold shadow-sm">
                <i class="fa-solid fa-eye me-2"></i> Keep Running & View Progress
            </a>
            
            <div class="d-flex align-items-center my-2">
                <hr class="flex-grow-1">
                <span class="mx-3 text-muted small text-uppercase fw-bold">OR</span>
                <hr class="flex-grow-1">
            </div>

            <form action="{{ route('audit.audits.resolve-stuck') }}" method="POST" onsubmit="return confirm('This will mark the current audit as failed and immediately start a new one. Are you sure?')">
                @csrf
                <input type="hidden" name="run_id" value="{{ $stuckRun->id }}">
                <input type="hidden" name="action" value="cancel_and_new">
                <button type="submit" class="btn btn-outline-danger rounded-pill py-2 w-100 fw-bold">
                    <i class="fa-solid fa-xmark me-2"></i> Cancel Current Audit & Start New
                </button>
            </form>
        </div>
        
        <div class="mt-4 pt-3 border-top">
            <a href="{{ route('audit.dashboard') }}" class="text-decoration-none text-muted small hover-primary">
                <i class="fa-solid fa-arrow-left me-1"></i> Return to Dashboard
            </a>
        </div>
    </div>
</div>
@endsection
