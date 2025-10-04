<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Admin;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        if (Auth::check()) {
            $user = Auth::user();


            if ($user->hasRole('admin')) {

                $subscription = Subscription::where(['user_id' => $user->id, 'status' => 'active'])->first();
            } else {
                $adminId = getAdminIdByUserRole();
                // dd($adminId);
                $user_id = Admin::findOrFail($adminId);

                $subscription = Subscription::where(['user_id' => $user_id->user_id, 'status' => 'active'])->first();
            }
            // Check if the user has an active subscription

            if (strpos($request->getRequestUri(), '/subscription-plan') !== false) {

                return $next($request);
            }
            if (!$subscription) {
                // Handle subscription not acti ve, redirect or return an error response
                return redirect()
                    ->route('subscription-plan.index')
                    ->with('error', 'You have no active subscription. Please buy a subscription.');
            }
        }
        return $next($request);
    }
}