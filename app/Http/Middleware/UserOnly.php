<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

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
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'UserOnly Unauthorized',
                ], 500);
            }
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'error' => 'UserOnly Unauthorized',
                'message' => $e->getMessage()
            ], 500);
        }

        $user = Auth::user();
        $Profile_LastActive = new DateTime();
        $Profile_LastActive = $Profile_LastActive->format('Y-m-d H:i:s');
        $updateActivity = User::where('Profile_ID', $user->Profile_ID)->update(['Profile_LastActive' => $Profile_LastActive]);
        return $next($request);
        /*if (Auth::check()) {
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
        ], 401);*/
    }
}
