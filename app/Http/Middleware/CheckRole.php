<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {

        if (auth()->check()) {
            // Check if the user has the role of superAdmin
            if (auth()->user()->hasRole('superadmin') || auth()->user()->hasRole('manager')) {
                return redirect(route('superadmin.panel'));
                // Redirect to the superadmin panel or perform any other action
                // abort(403, 'Unauthorized action.');

            }
        }

        // If the user is not authenticated or doesn't have the role of superadmin,
        // simply proceed with the request
        return $next($request);
    }
}
