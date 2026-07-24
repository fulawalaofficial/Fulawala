<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomOrderController;
use App\Http\Controllers\Api\EventBookingController;
use App\Http\Controllers\Api\FlowerProductController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PoojaPacketController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\SubscriptionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public API routes
|--------------------------------------------------------------------------
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/flower-package', [PoojaPacketController::class, 'index']);
Route::get('/pooja-packets/{poojaPacket}', [PoojaPacketController::class, 'show']);
Route::get('/flowers', [FlowerProductController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Authenticated API routes
|--------------------------------------------------------------------------
*/


Route::get(
    '/profile-images/{filename}',
    [ProfileController::class, 'showPhotoFile']
)
    ->where('filename', '[A-Za-z0-9._-]+')
    ->name('profile.images.show');

    
Route::middleware('auth:sanctum')->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Profile and profile photo
    |--------------------------------------------------------------------------
    |
    | Important:
    | There is only one POST /profile/photo route.
    | The old duplicate uploadPhoto route caused Laravel to call a method that
    | did not exist in ProfileController.
    |
    */

    Route::get('/profile', [ProfileController::class, 'show']);
    Route::get('/profile/photo', [ProfileController::class, 'getPhoto']);
    Route::post('/profile/photo', [ProfileController::class, 'updatePhoto']);
    Route::delete('/profile/photo', [ProfileController::class, 'deletePhoto']);

    // Optional legacy delete URL for an older mobile application build.
    Route::delete('/profile/photo-delete', [ProfileController::class, 'deletePhoto']);

    Route::post('/logout', [AuthController::class, 'logout']);

    /*
    |--------------------------------------------------------------------------
    | Addresses
    |--------------------------------------------------------------------------
    |
    | Address methods belong to AddressController. They must not point to the
    | ProfileController::show method.
    |
    */

    Route::get('/addresses', [AddressController::class, 'index']);
    Route::post('/addresses-create', [AddressController::class, 'store']);
    Route::get('/addresses/{address}', [AddressController::class, 'show']);
    Route::patch('/addresses/{address}', [AddressController::class, 'update']);
    Route::patch('/addresses/{address}/default', [AddressController::class, 'makeDefault']);
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy']);

    /*
    |--------------------------------------------------------------------------
    | Orders, events, subscriptions and payments
    |--------------------------------------------------------------------------
    */

    Route::post('/custom-orders', [CustomOrderController::class, 'store']);
    Route::get('/my-orders', [CustomOrderController::class, 'myOrders']);

    Route::post('/event-bookings', [EventBookingController::class, 'store']);
    Route::get('/my-quotations', [EventBookingController::class, 'myQuotations']);
    Route::post(
        '/quotations/{quotation}/accept',
        [EventBookingController::class, 'acceptQuotation']
    );

    Route::post('/subscriptions', [SubscriptionController::class, 'store']);
    Route::get('/my-subscriptions', [SubscriptionController::class, 'mySubscriptions']);

    Route::post('/payments/create-order', [PaymentController::class, 'createOrder']);
    Route::post('/payments/verify', [PaymentController::class, 'verify']);
    Route::get('/payments/history', [PaymentController::class, 'history']);

    Route::get(
    '/home/current-month-subscriptions',
    [
        HomeController::class,
        'currentMonthSubscriptions',
    ]
);
});
