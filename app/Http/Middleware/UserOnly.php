<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UserOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next): Response
    {
        //if (Auth::check()) {
        if (Auth::user()) {
            return $next($request);
        }

        //Auth::logout();
        // 401 Unauthorized
        return response()->json([
                'error' => 'UserOnly Unauthorized',
                'check' => Auth::check(),
                'user' => Auth::user(),
                'request' => $request->user(),
            ], 401);
    }
}
