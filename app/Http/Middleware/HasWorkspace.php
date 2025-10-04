<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class HasWorkspace
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */



public function handle(Request $request, Closure $next)
{
    $user = getAuthenticatedUser();


    if ($user && ($user->hasRole('manager') || $user->hasRole('superadmin'))) {
        return $next($request);
    }


    $isApiRequest = $request->get('isApi',false);

    // dd($isApiRequest);
    $workspaceId = $isApiRequest
        ? $request->header('workspace-id')
        : session('workspace_id');

    // dd($workspaceId);
    if (!$workspaceId || $workspaceId == 0) {
        $message = get_label('must_workspace_participant', 'You must be a participant in at least one workspace');

        return $isApiRequest
            ? response()->json(['error' => true, 'message' => $message], 403)
            : redirect(route('home.index'))->with('error', $message);
    }


    return $next($request);
}
}
