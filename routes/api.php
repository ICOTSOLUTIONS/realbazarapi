<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{Admin,App};

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
        Route::post('/signup',[Admin\AuthController::class,'signup']);
        Route::post('/login',[Admin\AuthController::class,'login']);
        Route::post('/reset',[Admin\AuthController::class,'reset']);
        Route::post('/forgot',[Admin\AuthController::class,'forgot']);
Route::middleware('auth:api')->group( function () {
        Route::get('profile',[Admin\AuthController::class,'edit_profile']);
        Route::post('profile/update',[Admin\AuthController::class,'update_profile']);
});
