<?php
use Firebase\JWT\JWT;
use App\Models\User;
use Firebase\JWT\Key;

// JWT
if(!function_exists('getJWT')) {
  function getJWT() {
    $header = request()->header('Authorization');
    $header = ($header) ? explode(' ',$header) : null;
    if(!$header) return null;
    $token = (count($header) > 1) ? $header[1] : null;
    return $token;
  }
}

if(!function_exists('createJWT')) {
  function createJWT($user) {
    $key = env('JWT_SECRET');
    $tokenTime = env('JWT_TIME_TO_LIVE');
    $requestTime = now()->timestamp;
    $requestExpired = $requestTime + $tokenTime;
        
    $payload = [
      'user_id' => $user->id,
      'name' => $user->name,
      'email' => $user->email,
      'role' => $user->role->name,
      'iat' => $requestTime,
      'exp' => $requestExpired
    ];

    $token = JWT::encode($payload, $key, 'HS256');
    return $token;
  }
}

if(!function_exists('createRefreshJWT')) {
  function createRefreshJWT($user) {
    $key = env('REFRESH_JWT_SECRET');
    $tokenTime = env('REFRESH_TOKEN_TIME_TO_LIVE');
    $requestTime = now()->timestamp;
    $requestExpired = $requestTime + $tokenTime;
        
    $payload = [
      'user_id' => $user->id,
      'name' => $user->name,
      'email' => $user->email,
      'role' => $user->role->name,
      'iat' => $requestTime,
      'exp' => $requestExpired
    ];

    $key = JWT::encode($payload, $key, 'HS256');
    return $key;
  }
}

if(!function_exists('decodeJWT')) {
  function decodeJWT($token, $key) {
    try {
      $result = JWT::decode($token, new Key($key, 'HS256'));
      return $result;
    } catch (Exception $e) {
      return null;
    }
  }
}

if(!function_exists('getContentJWT')) {
  function getContentJWT() {
    $token = getJWT();
    $key = env('JWT_SECRET');
    $decoded = decodeJWT($token,$key);
    return $decoded;
  }
}

if(!function_exists('getAuthUser')) {
  function getAuthUser($request) {
    $tokenUser = $request->cookie('refreshToken');
    $keyUser = env('REFRESH_JWT_SECRET');
    $decodedUser = decodeJWT($tokenUser, $keyUser);
    $user = User::where('id',$decodedUser->user_id)->first();
    return $user;
  }
}