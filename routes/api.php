<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Controller
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\MessageController;
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
    Route::get('/me',[UserController::class, 'me']);
    Route::post('/register',[UserController::class, 'register'])->middleware('verify.token');
    Route::post('/login',[UserController::class, 'login']);
    Route::post('/refresh',[UserController::class, 'refresh']);
    Route::put('/logout',[UserController::class, 'logout'])->middleware('verify.token');
    Route::delete('/delete/{id}',[UserController::class, 'delete']);
});
Route::group(['middleware' => ['verify.token']], function() {
    Route::apiResource('/orders',OrderController::class);
    Route::put('/orders/{order}/finished',[OrderController::class, 'status']);
    Route::put('/orders/{order}/confirm',[OrderController::class, 'confirm']);
    Route::post('/orders/register-order',[OrderController::class, 'register_order']);
});
