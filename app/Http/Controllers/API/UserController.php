<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\AccessTokens;

class UserController extends Controller
{
    public function register(Request $request)
    {
        if(!Gate::forUser(getContentJWT())->allows('is-admin-super')) return Response('',403);

        $validator = Validator::make($request->all(),[
            'name' => 'required|alpha:ascii|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|max:20'
        ]);   

        if($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Valdidation failed',
                'errors' => $validator->errors(),
            ],400);
        }

        $validated = $validator->validated();
        $validated['password'] = bcrypt($validated['password']);

        User::factory()->create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'The user has successfully registered'
        ],201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'email' => 'required|email',
            'password' => 'required',
        ]);   

        if($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Valdidation failed',
                'errors' => $validator->errors(),
            ],400);
        }

        if(!Auth::attempt($validator->validated())) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Authentication failed'
            ],401);
        }

        $user = User::where('id',Auth::user()->id)->first();
        if(!$user) return Response('',500);

        $token = createJWT($user);
        $refreshToken = createRefreshJWT($user);
        $tokenTime = env('JWT_TIME_TO_LIVE');
        $cookieMinute = env('REFRESH_TOKEN_TO_LIVE');

        User::where('id',$user->id)->update([
            'access_token' => $refreshToken
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'The user has successfully logged in',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'authorization' => [
                'access_token' => $token,
                'type' => 'bearer',
                'expires_in' => $tokenTime.'s'
            ]
        ],201)->cookie('refreshToken',$refreshToken,$cookieMinute);
    }

    public function refresh(Request $request)
    {
        $token = $request->cookie('refreshToken');
        if(!$token) return Response('',204);

        $user = User::where('access_token',$token)->first();
        if(!$user) return Response('',403);

        $keyRefreshToken = env('REFRESH_JWT_SECRET');
        $tokenTime = env('JWT_TIME_TO_LIVE');

        $decoded = decodeJWT($token,$keyRefreshToken);
        if(!$decoded) return Response('',403);

        $newToken = createJWT($user);
            
        return response()->json([
            'status' => 'success',                
            'message' => 'The user has successfully refreshed the token',
            'authorization' => [
                'access_token' => $newToken,
                'type' => 'bearer',
                'expires_in' => $tokenTime.'s'
            ]
        ],201);
    }

    public function logout(Request $request) 
    {
        $token = $request->cookie('refreshToken');
        if(!$token) return Response('',401);
        
        $user = User::select('id','access_token')->where('access_token',$token)->first();
        if(!$user) return Response('',403);

        User::where('id',$user->id)->update([
            'access_token' => null
        ]);

        Auth::logout();

        return response()->json([
            'status' => 'success',
            'message' => 'The user has successfully logged out'
        ])->withoutCookie('refreshToken');
    }
}
