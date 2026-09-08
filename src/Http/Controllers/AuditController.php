<?php

namespace ThePrimeStudio\Audit\Http\Controllers;

use Illuminate\Routing\Controller;
use ThePrimeStudio\Audit\Models\AuditRun;
use ThePrimeStudio\Audit\Jobs\RunAuditJob;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index()
    {
        $runs = AuditRun::orderBy('id', 'desc')->paginate(15);
        return view('audit::audits.index', compact('runs'));
    }

    public function run(Request $request)
    {
        // Check if an audit is already running or stuck
        $stuckRun = AuditRun::whereIn('status', ['running', 'pending'])->first();
        if ($stuckRun) {
            return view('audit::audits.stuck', ['stuckRun' => $stuckRun]);
        }

        $run = AuditRun::create([
            'status' => 'pending',
            'started_at' => now(),
        ]);

        if (config('queue.default') !== 'sync') {
            dispatch(new RunAuditJob($run));
            return redirect()->route('audit.audits.progress')->with('success', 'Audit started in the background.');
        } else {
            // Run synchronously
            try {
                $job = new RunAuditJob($run);
                $job->handle();
                return redirect()->route('audit.dashboard')->with('success', 'Audit completed successfully.');
            } catch (\Exception $e) {
                $run->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'completed_at' => now(),
                ]);
                return redirect()->route('audit.audits.index')->with('error', 'Audit failed: ' . $e->getMessage());
            }
        }
    }

    public function resolveStuck(Request $request)
    {
        $request->validate([
            'run_id' => 'required|integer',
            'action' => 'required|in:mark_failed,mark_completed,delete,cancel_and_new',
        ]);

        $run = AuditRun::findOrFail($request->run_id);

        switch ($request->action) {
            case 'cancel_and_new':
                $run->update([
                    'status' => 'failed',
                    'error_message' => 'Cancelled by user to start a new audit.',
                    'completed_at' => now(),
                ]);
                return $this->run(new Request());

            case 'mark_failed':
                $run->update([
                    'status' => 'failed',
                    'error_message' => 'Manually marked as failed by user.',
                    'completed_at' => now(),
                ]);
                return redirect()->route('audit.audits.index')->with('success', 'Previous audit marked as failed.');

            case 'mark_completed':
                $run->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
                return redirect()->route('audit.audits.index')->with('success', 'Previous audit marked as completed.');

            case 'delete':
                $run->delete();
                return redirect()->route('audit.audits.index')->with('success', 'Previous audit deleted.');
        }

        return redirect()->route('audit.audits.index');
    }

    public function progress()
    {
        $run = AuditRun::whereIn('status', ['pending', 'running'])->orderBy('id', 'desc')->first();
        if (!$run) {
            return redirect()->route('audit.dashboard');
        }

        return view('audit::audits.progress', compact('run'));
    }

    /**
     * JSON endpoint for live progress polling from the progress page.
     */
    public function progressData()
    {
        $run = AuditRun::whereIn('status', ['pending', 'running', 'completed', 'failed'])
            ->orderBy('id', 'desc')
            ->first();

        if (!$run) {
            return response()->json(['status' => 'none']);
        }

        return response()->json([
            'id' => $run->id,
            'status' => $run->status,
            'progress_percent' => $run->progress_percent ?? 0,
            'progress_message' => $run->progress_message ?? 'Waiting...',
            'urls_crawled' => $run->urls_crawled ?? 0,
        ]);
    }

    public function show($id)
    {
        $run = AuditRun::with(['issues'])->findOrFail($id);
        
        // Eager load urls for displaying in the history
        // You might want to paginate this if there are thousands of URLs
        $urls = $run->urls()->paginate(50);
        
        return view('audit::audits.show', compact('run', 'urls'));
    }

    public function delete(AuditRun $run)
    {
        $run->delete();
        return redirect()->route('audit.audits.index')->with('success', 'Audit history deleted.');
    }
}

