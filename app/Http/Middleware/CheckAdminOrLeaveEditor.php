<?php

// app/Http/Middleware/CheckAdminOrLeaveEditor.php

namespace App\Http\Middleware;

use Auth;
use Closure;
use App\Models\LeaveEditor;
use App\Models\LeaveRequest;

class CheckAdminOrLeaveEditor
{
    public function handle($request, Closure $next)
    {


        $user = getAuthenticatedUser();

        // Check if the user is an admin or a leave editor based on their presence in the leave_editors table
        if ($user->hasRole('admin') || LeaveEditor::where('user_id', $user->id)->exists()) {
            return $next($request);
        }

        // Check if the user is the creator of the leave request and the leave status is pending
        $leaveRequestId = $request->route('id');
        $leaveRequest = LeaveRequest::find($leaveRequestId);
        if (!$leaveRequest) {
            return response()->json(['error' => true, 'message' => 'Leave request not found']);
        }

        if ($leaveRequest->user_id == $user->id && $leaveRequest->status == 'pending') {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['error' => true, 'message' => get_label('not_authorized', 'You are not authorized to perform this action.')]);
        }
        return redirect('/home')->with('error', get_label('not_authorized', 'You are not authorized to perform this action.'));

    }
}
