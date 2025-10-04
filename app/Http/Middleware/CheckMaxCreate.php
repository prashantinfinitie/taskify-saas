<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Admin;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckMaxCreate
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() || Auth::guard('sanctum')->check()) {


            $user = Auth::user();

            $adminId = getAdminIdByUserRole();
            $admin = Admin::findOrFail($adminId);
            $subscription = Subscription::where(['user_id' => $admin->user_id, 'status' => 'active'])->first();
            $projects = $admin->projects()->count();
            $users = $admin->teamMembers()->count();
            $clients = $admin->clients()->count();
            $workspaces = $admin->workspaces()->count();
            $subscriptionFeatures =  json_decode($subscription->features, true);

            // dd($request);
            $currentRoute = $request->route()->getName();

            // $projects =

            if (str_contains($currentRoute, '.create') || str_contains($currentRoute, '.duplicate') || str_contains($currentRoute, '.store')) {

                $resource = explode('.', $currentRoute)[0];


                switch ($resource) {
                    case 'clients':
                        if ($subscriptionFeatures['max_clients'] != -1 && $clients >= $subscriptionFeatures['max_clients']) {
                            $message = 'You have reached the maximum limit for creating clients.';
                            return $this->sendResponse($request, $message);
                        }
                        break;
                    case 'projects':
                        if ($subscriptionFeatures['max_projects'] != -1 && $projects >= $subscriptionFeatures['max_projects']) {
                            $message = 'You have reached the maximum limit for creating projects.';
                            return $this->sendResponse($request, $message);
                        }
                        break;
                    case 'users':
                        if ($subscriptionFeatures['max_team_members'] != -1 && $users >= $subscriptionFeatures['max_team_members']) {
                            $message = 'You have reached the maximum limit for creating team members.';
                            return $this->sendResponse($request, $message);
                        }
                        break;
                    case 'workspaces':
                        if ($subscriptionFeatures['max_workspaces'] != -1 && $workspaces >= $subscriptionFeatures['max_workspaces']) {
                            $message = 'You have reached the maximum limit for creating workspaces.';
                            return $this->sendResponse($request, $message);
                        }
                        break;
                }

            }
        }
        return $next($request);
    }
    private function sendResponse($request, $message)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => true, 'message' => $message], 422);
        } else {
            return redirect()->back()->with('error', $message);
        }
    }
}
