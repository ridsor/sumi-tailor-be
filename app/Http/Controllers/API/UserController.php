<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\AccessTokens;

class UserController extends Controller
{
    public function register(Request $request)
    {
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

        $cookieMinute = 24*60*60;
        $keyToken = env('JWT_SECRET');
        $keyRefreshToken = env('REFRESH_JWT_SECRET');
        $tokenTime = env('JWT_TIME_TO_LIVE');
        $requestTime = now()->timestamp;
        $requestExpired = $requestTime + $tokenTime;
        
        $payloadToken = [
            'user_id' => Auth::user()->id,
            'name' => Auth::user()->name,
            'email' => Auth::user()->email,
            'iat' => $requestTime,
            'exp' => $requestExpired
        ];

        $payloadRefreshToken = [
            'user_id' => Auth::user()->id,
            'name' => Auth::user()->name,
            'email' => Auth::user()->email,
            'iat' => $requestTime,
            'exp' => $requestTime + $cookieMinute,
        ];

        $token = createToken($payloadToken, $keyToken);
        $refreshToken = createToken($payloadRefreshToken, $keyRefreshToken);

        User::where('id',Auth::user()->id)->update([
            'access_token' => $refreshToken
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'The user has successfully logged in',
            'user' => [
                'id' => Auth::user()->id,
                'name' => Auth::user()->name,
                'email' => Auth::user()->email,
            ],
            'authorization' => [
                'access_token' => $token,
                'type' => 'bearer',
                'expires_in' => $tokenTime.'s'
            ]
        ],201)->cookie('refreshToken',$refreshToken,$cookieMinute);
    }

    public function logout(Request $request) {
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
