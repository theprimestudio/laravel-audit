<?php

namespace ThePrimeStudio\Audit\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AuthorizeAudit
{
    public function handle(Request $request, Closure $next, string $permissionKey)
    {
        $permissions = config('audit.permissions', []);
        
        // Find permission name in key (e.g. dashboard, actions.run_audit)
        $permissionName = data_get($permissions, $permissionKey);

        if (!$permissionName) {
            abort(403, 'Unauthorized action.');
        }

        if (!Gate::allows($permissionName)) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
