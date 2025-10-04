<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\LeaveEditor;
use App\Models\User;

class CheckAdminOrUser
{
    public function handle(Request $request, Closure $next)
    {
        // Fetch user from session or token (depends on your auth setup)
        $user = getAuthenticatedUser(); // This should handle both Web (session) and API (token/header) cases

        if (!$user) {
            return $this->unauthorizedResponse($request);
        }

        // Check if user is admin or leave editor
        $isAdmin = $user->hasRole('admin'); // You can customize this check
        $isLeaveEditor = LeaveEditor::where('user_id', $user->id)->exists();
        $isNormalUser = $user->hasRole('user'); // Optional: if you want to allow normal users

        if ($isAdmin || $isLeaveEditor || $isNormalUser) {
            return $next($request);
        }

        return $this->unauthorizedResponse($request);
    }

    protected function unauthorizedResponse(Request $request)
    {
        $message = get_label('not_authorized', 'You are not authorized to perform this action.');

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['error' => true, 'message' => $message], 403);
        }

        return redirect(route('home.index'))->with('error', $message);
    }
}
