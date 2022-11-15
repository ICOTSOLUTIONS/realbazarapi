<?php

use App\Models\DemandProduct;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return DemandProduct::where('created_at', '<=', Carbon::now()->subDay())->get();
    // return Carbon::now()->subDay();
});
