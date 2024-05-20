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
      'image' => $user->image,
      'role' => $user->role->name,
      'iat' => $requestTime,
      'exp' => $requestExpired
    ];
    
    $token = JWT::encode($payload, $key, 'HS256');
    return $token;
  }
}

if(!function_exists('createRefreshJWT')) {
  function createRefreshJWT($user, $time) {
    $key = env('REFRESH_JWT_SECRET');
    $requestTime = now()->timestamp;
    $requestExpired = $requestTime + $time;
    
    $payload = [
      'user_id' => $user->id,
      'name' => $user->name,
      'email' => $user->email,
      'image' => $user->image,
      'role' => $user->role->name,
      'iat' => $requestTime,
      'exp' => $requestExpired
    ];
    
    $token = JWT::encode($payload, $key, 'HS256');
    return $token;
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

if(!function_exists('getAuthUser')) {
  function getAuthUser() {
    $tokenUser = getJWT();
    if(!$tokenUser) return null;   
    $keyUser = env('JWT_SECRET');
    $decodedUser = decodeJWT($tokenUser, $keyUser);
    if(!$decodedUser) return null;
    $user = User::where('id',$decodedUser->user_id)->first();
    return $user;
  }
}