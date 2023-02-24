<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HeaderMiddleware
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
        if ($request->hasHeader('Accept')) {
            if($request->header('Accept') != 'application/json'){
                return sendError('Send Accept Type First');
            }
            return $next($request);
        } else {
            return $next($request);
        }
    }
}
