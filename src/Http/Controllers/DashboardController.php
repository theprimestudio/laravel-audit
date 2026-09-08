<?php

namespace ThePrimeStudio\Audit\Http\Controllers;

use Illuminate\Routing\Controller;
use ThePrimeStudio\Audit\Models\AuditRun;
use ThePrimeStudio\Audit\Models\AuditUrl;
use ThePrimeStudio\Audit\Models\AuditIssue;

class DashboardController extends Controller
{
    public function index()
    {
        $lastRun = AuditRun::orderBy('id', 'desc')->first();
        $history = AuditRun::orderBy('id', 'desc')->take(10)->get();

        return view('audit::dashboard.index', [
            'lastRun' => $lastRun,
            'history' => $history,
        ]);
    }

    public function websites()
    {
        $lastRun = AuditRun::orderBy('id', 'desc')->first();
        $urls = $lastRun ? $lastRun->urls()->with('httpResult')->get() : collect();

        return view('audit::dashboard.websites', [
            'lastRun' => $lastRun,
            'urls' => $urls,
        ]);
    }

    public function seo()
    {
        $lastRun = AuditRun::orderBy('id', 'desc')->first();
        $seoResults = $lastRun ? AuditUrl::where('audit_run_id', $lastRun->id)->with('seoResult')->get() : collect();

        return view('audit::dashboard.seo', [
            'lastRun' => $lastRun,
            'seoResults' => $seoResults,
        ]);
    }

    public function security()
    {
        $lastRun = AuditRun::orderBy('id', 'desc')->first();
        $securityResults = $lastRun ? AuditUrl::where('audit_run_id', $lastRun->id)->with('securityResult')->get() : collect();

        return view('audit::dashboard.security', [
            'lastRun' => $lastRun,
            'securityResults' => $securityResults,
        ]);
    }

    public function performance()
    {
        $lastRun = AuditRun::orderBy('id', 'desc')->first();
        $performanceResults = $lastRun ? AuditUrl::where('audit_run_id', $lastRun->id)->with('performanceResult')->get() : collect();

        return view('audit::dashboard.performance', [
            'lastRun' => $lastRun,
            'performanceResults' => $performanceResults,
        ]);
    }

    public function laravel()
    {
        $lastRun = AuditRun::orderBy('id', 'desc')->first();
        $routes = $lastRun ? $lastRun->routes()->get() : collect();
        $exceptions = $lastRun ? $lastRun->exceptions()->get() : collect();
        $queries = $lastRun ? $lastRun->queries()->orderBy('time_ms', 'desc')->take(50)->get() : collect();

        return view('audit::dashboard.laravel', [
            'lastRun' => $lastRun,
            'routes' => $routes,
            'exceptions' => $exceptions,
            'queries' => $queries,
        ]);
    }

    public function api()
    {
        $lastRun = AuditRun::orderBy('id', 'desc')->first();
        
        // Get URLs starting with api/ or configured
        $apiResults = $lastRun ? $lastRun->urls()->where(function($q) {
            $q->where('url', 'like', '%/api/%')->orWhere('url', 'like', '%/api');
        })->with('httpResult')->get() : collect();

        // Also get registered API routes
        $registeredApiRoutes = $lastRun ? $lastRun->routes()->where('uri', 'like', 'api/%')->get() : collect();

        return view('audit::dashboard.api', [
            'lastRun' => $lastRun,
            'apiResults' => $apiResults,
            'registeredApiRoutes' => $registeredApiRoutes,
        ]);
    }
}
