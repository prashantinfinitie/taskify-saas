<?php
namespace App\Http\Middleware;

use Closure;

class IsApi
{
    public function handle($request, Closure $next)
    {
        // dd($request);
        $request->attributes->set('isApi', true);
        return $next($request);
    }
}
