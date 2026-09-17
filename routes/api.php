<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomOrderController;
use App\Http\Controllers\Api\EventBookingController;
use App\Http\Controllers\Api\FlowerProductController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PoojaPacketController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SubscriptionController;
use Illuminate\Support\Facades\Route;

/* Public authentication routes with brute-force protection. */
Route::post('/register', [AuthController::class, 'register'])
    ->middleware('throttle:3,1');

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1');

Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])
    ->middleware('throttle:3,1');

Route::post('/reset-password', [AuthController::class, 'resetPassword'])
    ->middleware('throttle:5,1');

Route::get('/flower-package', [PoojaPacketController::class, 'index']);

Route::get('/pooja-packets/{poojaPacket}', [PoojaPacketController::class, 'show'])
    ->whereNumber('poojaPacket');

Route::get('/flowers', [FlowerProductController::class, 'index']);

Route::get('/profile-images/{filename}', [ProfileController::class, 'showPhotoFile'])
    ->where('filename', '[A-Za-z0-9._-]+')
    ->name('profile.images.show');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/home', [HomeController::class, 'currentMonthSubscriptions']);
    Route::get('/home/current-month-subscriptions', [HomeController::class, 'currentMonthSubscriptions']);

    Route::get('/profile', [ProfileController::class, 'show']);
    Route::get('/profile/photo', [ProfileController::class, 'getPhoto']);
    Route::post('/profile/photo', [ProfileController::class, 'updatePhoto']);
    Route::delete('/profile/photo', [ProfileController::class, 'deletePhoto']);
    Route::delete('/profile/photo-delete', [ProfileController::class, 'deletePhoto']);

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/device-token', [AuthController::class, 'updateDeviceToken']);

    Route::get('/addresses', [AddressController::class, 'index']);
    Route::post('/addresses', [AddressController::class, 'store']);
    Route::post('/addresses-create', [AddressController::class, 'store']);
    Route::get('/addresses/{address}', [AddressController::class, 'show'])
        ->whereNumber('address');
    Route::match(['put', 'patch'], '/addresses/{address}', [AddressController::class, 'update'])
        ->whereNumber('address');
    Route::patch('/addresses/{address}/default', [AddressController::class, 'makeDefault'])
        ->whereNumber('address');
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy'])
        ->whereNumber('address');

    Route::post('/custom-orders', [CustomOrderController::class, 'store']);
    Route::get('/my-orders', [CustomOrderController::class, 'myOrders']);

    Route::post('/event-bookings', [EventBookingController::class, 'store']);
    Route::get('/my-quotations', [EventBookingController::class, 'myQuotations']);
    Route::post('/quotations/{quotation}/accept', [EventBookingController::class, 'acceptQuotation'])
        ->whereNumber('quotation');

    Route::post('/subscriptions', [SubscriptionController::class, 'store']);
    Route::get('/my-subscriptions', [SubscriptionController::class, 'mySubscriptions']);

    Route::post('/payments/create-order', [PaymentController::class, 'createOrder']);
    Route::post('/payments/verify', [PaymentController::class, 'verify']);
    Route::get('/payments/history', [PaymentController::class, 'history']);
});
