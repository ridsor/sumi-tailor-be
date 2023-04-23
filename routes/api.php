<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Controller
use App\Http\Controllers\API\PesananController;
use App\Http\Controllers\API\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'auth'], function() {
    Route::post('/register',[UserController::class, 'register']);
    Route::post('/login',[UserController::class, 'login']);
    Route::delete('/logout',[UserController::class, 'logout']);
});
Route::group([], function() {
    Route::resource('/order',PesananController::class);
});
