<?php

namespace ThePrimeStudio\Audit\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use ThePrimeStudio\Audit\Models\AuditIssue;

class IssueController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditIssue::query();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->whereIn('status', ['open', 'acknowledged']);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%')
                  ->orWhere('url', 'like', '%' . $request->search . '%');
            });
        }

        $issues = $query->orderBy('severity', 'asc')->paginate(20);

        return view('audit::issues.index', compact('issues'));
    }

    public function resolve(AuditIssue $issue)
    {
        $issue->update(['status' => 'resolved']);
        return back()->with('success', 'Issue marked as resolved.');
    }

    public function ignore(AuditIssue $issue)
    {
        $issue->update(['status' => 'ignored']);
        return back()->with('success', 'Issue ignored.');
    }

    public function delete(AuditIssue $issue)
    {
        $issue->delete();
        return back()->with('success', 'Issue deleted.');
    }
}
