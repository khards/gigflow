<?php

use App\Booking\Controllers\Api\Cart;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// User routes.
Route::prefix('/user')->group(function () {

    //Login and return an api token
    //Route::post('/login', 'api\v1\LoginController@login');

    //
    //Route::middleware('auth:api')->get('test', 'api\v1\LoginController@test');
});

// Availability routes
Route::prefix('/availability')->middleware('auth:api')->group(function () {
    Route::post('/check', '\App\Booking\Controllers\Api\Availability@check')->name('api.availability.check');
});

//Products routes
Route::prefix('/products')->middleware('auth:api')->group(function () {
    Route::get('/', '\App\Booking\Controllers\Api\Products@get')->name('api.products.get');
});

//Order Form routes
Route::prefix('/orderform')->middleware('auth:api')->group(function () {
    Route::get('/', ['\App\Booking\Controllers\Api\OrderForm', 'get'])->name('api.orderform');
});

//Cart routes
Route::prefix('/cart')->middleware('auth:api')->group(function () {
    Route::patch('/', [Cart::class, 'update'])->name('api.cart.update');
});

//Checkout routes
Route::prefix('/checkout')->middleware('auth:api')->group(function () {
    Route::post('/order', [\App\Booking\Controllers\Api\Checkout::class, 'order'])->name('api.checkout.order');
});
