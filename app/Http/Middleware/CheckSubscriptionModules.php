<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionModules
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Check if the user is logged in and has a subscription
        if ($user && $user->subscriptions()->where('status', 'active')->exists()) {
            // Get the user's subscription modules
            $subscription = $user->subscriptions()->where('status', 'active')->first();
            $subscriptionModules = json_decode($subscription->features);
            // dd($subscriptionModules);
            $subscriptionModules = $subscriptionModules->modules;
            // Get modules defined in the config file
            $configModules = array_keys(Config::get('taskify.modules'));
            // dd($configModules);
            $extraModules = ['expenses', 'estimates-invoices', 'items', 'payments', 'payment-methods', 'taxes', 'units'];

            $configModules = array_merge($extraModules, $configModules);
            // dd($configModules);
            // Get the current route name

            $currentUri = $request->getPathInfo();
            $currentUriParts = explode('/', $currentUri);
            $currentRoute = end($currentUriParts);
            // dd($subscriptionModules);

            // dd(in_array($currentRoute, $subscriptionModules));

            // Check if the current route corresponds to a module defined in the config
            if (in_array($currentRoute, $configModules)) {
                switch ($currentRoute) {
                    case 'expenses':
                        $currentRoute = 'finance';
                        break;
                    case 'estimates-invoices':
                        $currentRoute = 'finance';
                        break;
                    case 'items':
                        $currentRoute = 'finance';
                        break;
                    case 'payments':
                        $currentRoute = 'finance';
                        break;
                    case 'payment-methods':
                        $currentRoute = 'finance';
                        break;
                    case 'taxes':
                        $currentRoute = 'finance';
                        break;
                    case 'units':
                        $currentRoute = 'finance';
                        break;
                    default:
                        $currentRoute;
                }

                // Check if any required module is missing from the subscription
                if (!in_array($currentRoute, $subscriptionModules)) {

                    // Module not allowed, redirect or return error response
                    return redirect()->route('home.index')->with('error', 'Access denied. You do not have permission to access this module.');
                }
            }
        }

        return $next($request);
    }
}
