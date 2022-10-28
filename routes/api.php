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
    Route::get('user/block/{id}', [Api\AuthController::class, 'userBlock']);

    //category
    Route::post('add/category', [Api\CategoryController::class, 'add']);
    Route::post('update/category', [Api\CategoryController::class, 'update']);
    Route::post('delete/category', [Api\CategoryController::class, 'delete']);

    //subcategory
    Route::post('add/subcategory', [Api\SubCategoryController::class, 'add']);
    Route::post('update/subcategory', [Api\SubCategoryController::class, 'update']);
    Route::post('delete/subcategory', [Api\SubCategoryController::class, 'delete']);

    //product
    Route::get('show/admin/products/{status?}', [Api\ProductController::class, 'showAdminProduct']);
    Route::get('show/seller/products', [Api\ProductController::class, 'showSellerProduct']);
    Route::post('add/product', [Api\ProductController::class, 'add']);
    Route::post('update/product', [Api\ProductController::class, 'update']);
    Route::post('delete/product', [Api\ProductController::class, 'delete']);
    Route::get('hard/delete/product/{id}', [Api\ProductController::class, 'hardDelete']);
    Route::get('all/hard/delete/product', [Api\ProductController::class, 'allHardDelete']);
    Route::get('show/delete/product', [Api\ProductController::class, 'showDeleteProduct']);
    Route::get('image/{id}', [Api\ProductController::class, 'image']);
    Route::post('delete/image', [Api\ProductController::class, 'deleteImage']);
    Route::post('add/image', [Api\ProductController::class, 'addImage']);
    Route::get('history/products', [Api\ProductController::class, 'historyProduct']);
    Route::post('add/history/products', [Api\ProductController::class, 'addHistoryProduct']);
    Route::post('like/products', [Api\ProductController::class, 'likeProduct']);
    Route::post('review/products', [Api\ProductController::class, 'reviewProduct']);
    Route::post('status/change/product', [Api\ProductController::class, 'productStatusChange']);

    //order
    Route::post('order', [Api\OrderController::class, 'order']);

    //package
    Route::get('/package', [Api\PackageController::class, 'show']);
    Route::post('add/package', [Api\PackageController::class, 'add']);
    Route::post('update/package', [Api\PackageController::class, 'update']);
    Route::post('delete/package', [Api\PackageController::class, 'delete']);
    Route::post('buy/package', [Api\PackageController::class, 'payment']);
    Route::post('exist/package/payment', [Api\PackageController::class, 'existPayment']);
    Route::get('subscription/expired', [Api\PackageController::class, 'packageExpiredPeriod']);

    //chat
    Route::get('/allMessages', [Api\ChatController::class, 'allMessages']);
    Route::get('/adminShowChat/{id}', [Api\ChatController::class, 'adminShowChat']);
    Route::post('/message', [Api\ChatController::class, 'message']);
    Route::post('/chats', [Api\ChatController::class, 'chat']);

    //banner
    Route::get('/banner/{section}', [Api\BannerController::class, 'banner']);
    Route::post('/add/banner', [Api\BannerController::class, 'addBanner']);
    Route::post('/update/banner', [Api\BannerController::class, 'updateBanner']);
    Route::post('/delete/banner', [Api\BannerController::class, 'deleteBanner']);

    //notification
    Route::get('/allNotification', [Api\NotificationController::class, 'allNotification']);
    Route::get('/showNotification', [Api\NotificationController::class, 'notification']);
    Route::get('/changeNotification', [Api\NotificationController::class, 'notification_change']);
    Route::post('/sendNotification', [Api\NotificationController::class, 'sendNotification']);
    Route::post('/singleNotification', [Api\NotificationController::class, 'singleNotification']);
});
//users
Route::get('wholesalers', [Api\AuthController::class, 'wholesaler']);
Route::get('users', [Api\AuthController::class, 'user']);
Route::get('retailers', [Api\AuthController::class, 'retailer']);

//product
Route::get('home/{role?}', [Api\ProductController::class, 'home']);
Route::get('products/{role?}', [Api\ProductController::class, 'show']);
Route::get('search/{name}/{role?}', [Api\ProductController::class, 'search']);
Route::post('show/product', [Api\ProductController::class, 'showProduct']);
Route::post('shop/product', [Api\ProductController::class, 'vendorProduct']);
Route::get('discount/product/{role?}', [Api\ProductController::class, 'discountProduct']);
Route::get('featured/product/{role?}', [Api\ProductController::class, 'featuredProduct']);
Route::get('newArrival/product/{role?}', [Api\ProductController::class, 'newArrivalProduct']);
Route::get('wholesaler/products', [Api\ProductController::class, 'wholesalerProducts']);

//category
Route::get('category', [Api\CategoryController::class, 'show']);
Route::get('subcategory', [Api\SubCategoryController::class, 'show']);
Route::get('show/subcategory/{id}', [Api\SubCategoryController::class, 'fetchSubCategory']);
Route::post('search/category', [Api\CategoryController::class, 'searchCategory']);
Route::post('subscribe', [Api\SettingController::class, 'subscribe']);

//banner
Route::get('/banners/{section}', [Api\BannerController::class, 'banners']);
Route::get('get/order', [Api\OrderController::class, 'show']);
