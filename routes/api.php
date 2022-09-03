<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{Api};

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
//auth
Route::post('/signup', [Api\AuthController::class, 'signup']);
Route::post('/login', [Api\AuthController::class, 'login']);
Route::post('/reset', [Api\AuthController::class, 'reset']);
Route::post('/forgot', [Api\AuthController::class, 'forgot']);
Route::middleware('auth:api')->group(function () {
        //auth
        Route::get('profile', [Api\AuthController::class, 'edit_profile']);
        Route::post('profile/update', [Api\AuthController::class, 'update_profile']);
        //product
        Route::post('add/product', [Api\ProductController::class, 'add']);
        Route::post('update/product', [Api\ProductController::class, 'update']);
        Route::post('delete/product', [Api\ProductController::class, 'delete']);
        Route::get('image/{id}', [Api\ProductController::class, 'image']);
        Route::post('delete/image', [Api\ProductController::class, 'deleteImage']);
        Route::post('add/image', [Api\ProductController::class, 'addImage']);
        Route::post('show/product', [Api\ProductController::class, 'showProduct']);
        //order
        Route::post('order', [App\Http\Controllers\OrderController::class, 'order']);
});
//product
Route::get('search/{name}', [Api\ProductController::class, 'search']);
Route::get('products', [Api\ProductController::class, 'show']);
//category
Route::get('category', [Api\CategoryController::class, 'show']);
Route::post('search/category', [Api\CategoryController::class, 'searchCategory']);
