<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckSuperAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            // Check if user has either superadmin or manager role
            if (!auth()->user()->hasAnyRole(['superadmin', 'manager'])) {
                // Deny access with a 403 Forbidden status code if neither role is present
                abort(403, 'Unauthorized action.');
            }
        }

        return $next($request);
    }
}
