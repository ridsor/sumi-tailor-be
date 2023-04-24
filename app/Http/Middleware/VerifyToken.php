<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
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
        $decoded = checkJWT($token,$key);
        if(!$decoded) return Response('',403);
        $user = User::where('id',$decoded->user_id)->first();
        if(!$user) return Response('',403);

        return $next($request);
    }
}
