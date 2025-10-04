<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Middleware\RoleMiddleware as SpatieRoleMiddleware;
class CustomRoleMiddleware extends SpatieRoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next, $role, $guard = null)
    {
        $guard = auth('web')->user() ? 'web' : 'client';
        $authGuard = Auth::guard($guard);

        if ($authGuard->guest()) {
            return redirect('/')->with('error', get_label('please login', 'Please login'));
        }

        $roles = is_array($role)
            ? $role
            : explode('|', $role);

        if (!$authGuard->user()->hasAnyRole($roles)) {
            return redirect(route('home.index'))->with('error', get_label('un_authorized_action', 'Un authorized action!'));
        }

        return $next($request);
    }
}
