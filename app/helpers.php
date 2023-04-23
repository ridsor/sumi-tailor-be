<?php
use Illuminate\Http\Response;
use Firebase\JWT\JWT;
// return response()->json();

// JWT
if(!function_exists('getJwt')) {
  function getJwt() {
    $header = request()->header('Authorization');
    $token = ($header) ? explode(' ',$header)[1] : "";
    if(!$token) return Response('',401);
    return $token;
  }
}

if(!function_exists('createToken')) {
  function createToken($payload, $key) {
    $key = JWT::encode($payload, $key, 'HS256');
    return $key;
  }
}