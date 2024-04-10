<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function me(Request $request) {
        $token = $request->cookie('refreshToken');
        if(!$token) return Response('',401);

        $user = User::where('access_token',$token)->first();
        if(!$user) return Response('',403);
        
        $keyRefreshToken = env('REFRESH_JWT_SECRET');
        $decoded = decodeJWT($token,$keyRefreshToken);
        if(!$decoded) return Response('',403);

        return Response([
            'status' => 'success',
            'message' => 'Successfully retrieved user data',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->name
            ]
        ]);
    }

    public function register(Request $request)
    {
        if(!Gate::forUser(getAuthUser($request))->allows('is-admin-super')) return Response('',403);

        $validator = Validator::make($request->all(),[
            'name' => 'required|max:100',
            'email' => 'required|email|unique:users|max:100',
            'password' => [
                'required',
                'max:20',
                Password::min(8)
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised(),
            ],
            'image' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);   
        
        if($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ],400);
        }
        
        $validated = $validator->safe()->only(['name', 'email','password']);

        if($request->file('image')) {
            $validated['image'] = Str::random(10) . time() . '.' . $request->image->extension(); 
            $request->image->move(public_path('images'), $validated['image']);
        }
        
        $validated['password'] = bcrypt($validated['password']);

        $role = Role::where('name','admin')->first();
        $validated['role_id'] = $role->id;

        User::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Register successfully'
        ],201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'email' => 'required|email|max:100',
            'password' => 'required|max:20|min:8',
            'remember_me' => 'boolean'
        ]);   
        
        if($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ],400);
        }

        $validated = $validator->safe()->only(['email','password']);
        $remember_me = $request->has('remember_me') ? $validator->safe()->only(['remember_me']) : false;

        if(!Auth::attempt($validated)) {
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
        $cookieMinute = ($remember_me) ? env('REFRESH_TOKEN_TO_LIVE',259200) : 7200;

        User::where('id',$user->id)->update([
            'access_token' => $refreshToken,
            'status' => now()
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Logged in successfully',
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
        ],201)->withCookie(
            cookie('refreshToken', $refreshToken, $cookieMinute)
        );
    }

    public function refresh(Request $request)
    {
        $token = $request->cookie('refreshToken');
        if(!$token) return Response('',401);

        $user = User::where('access_token',$token)->first();
        if(!$user) return Response('',403);

        $keyRefreshToken = env('REFRESH_JWT_SECRET');
        $tokenTime = env('JWT_TIME_TO_LIVE');

        $decoded = decodeJWT($token,$keyRefreshToken);
        if(!$decoded) return Response('',403);

        $newToken = createJWT($user);

        $user->status = now();
        $user->save();
            
        return response()->json([
            'status' => 'success',                
            'message' => 'Managed to refresh the token',
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
            'message' => 'Logged out successfully'
        ])->withoutCookie('refreshToken');
    }

    public function delete(Request $request, $id) {
        if(!Gate::forUser(getAuthUser($request))->allows('is-admin-super')) return Response('',403);

        $user = User::where('id',$id)->first();

        if(!$user) return Response([
            'status' => 'fail',
            'message' => 'User not found'
        ],404);

        User::destroy($user);

        return Response([
            'status' => 'success',
            'message' => 'Account has been deleted',
        ]);
    }

    public function update(Request $request, $id)
    {
        if(!Gate::forUser(getAuthUser($request))->allows('is-super-or-admin')) return Response('',403);

        $user = User::where('id',$id)->first();

        if(!$user) return Response([
            'status' => 'fail',
            'message' => 'User not found'
        ],404);

        $rules = [];

        if($request->input('profile')) {
            if($request->input('password')) {
                $rules['password'] = [
                    'required',
                    'max:20',
                    Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
                ];
            } else {
                $rules['name'] = 'required|max:100';

                if($user->email != $request->input('email')) {
                    $rules['email'] = 'required|email|unique:users|max:100';
                }
                if($request->file('image')) {
                    $rules['image'] = 'image|mimes:jpeg,png,jpg|max:1048';
                }
            }
        } else {
            $rules['name'] = 'required|max:100';

            if($user->email != $request->input('email')) {
                    $rules['email'] = 'required|email|unique:users|max:100';
                }
                
            $rules['password'] = [
                'required',
                'max:20',
                Password::min(8)
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised(),
            ];
            
            if($request->input('image')) {
                $rules['image'] = 'image|mimes:jpeg,png,jpg|max:2048';
            }
        }
        
        $messages = [
            'email.unique' => 'Email sudah ada',
        ];

        $validator = Validator::make($request->all(),$rules, $messages);

        if($validator->fails()) return response()->json([
            'status' => 'fail',
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ],400);

        $validated = [];
        if($request->input('profile')) {
            if($request->input('password')) {
                $validated = $validator->safe()->only(['password']);
            } else {
                $validated =  $validator->safe()->only(['name','email']);
            }
        } else {
            $validated =  $validator->safe()->only(['name','email','password']);
        }

        if($request->file('image')) {
            $validated['image'] = Str::random(10) . time() . '.' . $request->image->extension(); 
            $request->image->move(public_path('images'), $validated['image']);
        }

        User::where('id',$id)->update($validated);
    }
}
