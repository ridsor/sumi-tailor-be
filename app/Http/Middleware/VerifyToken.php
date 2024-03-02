<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class VerifyToken
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
        $token = getJWT();
        if(!$token) return Response('',401);
        $key = env('JWT_SECRET');
        $decoded = decodeJWT($token,$key);
        if(!$decoded) return Response('',401);
        $user = User::where('id',$decoded->user_id)->first();
        if(!$user) return Response('',401);
        
        return $next($request);
    }
}
