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
    Route::post('shop/followers', [Api\AuthController::class, 'shopFollow']);
    //category
    Route::post('add/category', [Api\CategoryController::class, 'add']);
    Route::post('update/category', [Api\CategoryController::class, 'update']);
    Route::post('delete/category', [Api\CategoryController::class, 'delete']);
    //subcategory
    Route::post('add/subcategory', [Api\SubCategoryController::class, 'add']);
    Route::post('update/subcategory', [Api\SubCategoryController::class, 'update']);
    Route::post('delete/subcategory', [Api\SubCategoryController::class, 'delete']);
    //product
    Route::post('add/product', [Api\ProductController::class, 'add']);
    Route::post('update/product', [Api\ProductController::class, 'update']);
    Route::post('delete/product', [Api\ProductController::class, 'delete']);
    Route::get('image/{id}', [Api\ProductController::class, 'image']);
    Route::post('delete/image', [Api\ProductController::class, 'deleteImage']);
    Route::post('add/image', [Api\ProductController::class, 'addImage']);
    Route::get('history/products', [Api\ProductController::class, 'historyProduct']);
    Route::post('add/history/products', [Api\ProductController::class, 'addHistoryProduct']);
    Route::post('like/products', [Api\ProductController::class, 'likeProduct']);
    Route::post('review/products', [Api\ProductController::class, 'reviewProduct']);

    //order
    Route::post('order', [Api\OrderController::class, 'order']);
    //package
    Route::get('/package', [Api\PackageController::class, 'show']);
    Route::post('add/package', [Api\PackageController::class, 'add']);
    Route::post('update/package', [Api\PackageController::class, 'update']);
    Route::post('delete/package', [Api\PackageController::class, 'delete']);

    //chat
    Route::get('/allMessages', [Api\ChatController::class, 'allMessages']);
    Route::post('/message', [Api\ChatController::class, 'message']);
    Route::post('/chats', [Api\ChatController::class, 'chat']);

    //banner
    Route::get('/banner/{section}', [Api\BannerController::class, 'banner']);
    Route::post('/add/banner', [Api\BannerController::class, 'addBanner']);
    Route::post('/update/banner', [Api\BannerController::class, 'updateBanner']);
    Route::post('/delete/banner', [Api\BannerController::class, 'deleteBanner']);

});
//product
Route::get('sellers', [Api\AuthController::class, 'showSeller']);
Route::get('search/{name}', [Api\ProductController::class, 'search']);
Route::get('products', [Api\ProductController::class, 'show']);
Route::post('shop/product', [Api\ProductController::class, 'vendorProduct']);
Route::post('show/product', [Api\ProductController::class, 'showProduct']);
//category
Route::get('category', [Api\CategoryController::class, 'show']);
Route::get('subcategory', [Api\SubCategoryController::class, 'show']);
Route::get('show/subcategory/{id}', [Api\SubCategoryController::class, 'fetchSubCategory']);
Route::post('search/category', [Api\CategoryController::class, 'searchCategory']);
Route::post('subscribe', [Api\SettingController::class, 'subscribe']);
