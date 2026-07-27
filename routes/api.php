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

/*
|--------------------------------------------------------------------------
| Public API routes
|--------------------------------------------------------------------------
*/

Route::post('/register', [
    AuthController::class,
    'register',
]);

Route::post('/login', [
    AuthController::class,
    'login',
]);

Route::get('/flower-package', [
    PoojaPacketController::class,
    'index',
]);

Route::get('/pooja-packets/{poojaPacket}', [
    PoojaPacketController::class,
    'show',
]);

Route::get('/flowers', [
    FlowerProductController::class,
    'index',
]);

/*
|--------------------------------------------------------------------------
| Public profile image route
|--------------------------------------------------------------------------
*/

Route::get('/profile-images/{filename}', [
    ProfileController::class,
    'showPhotoFile',
])
    ->where('filename', '[A-Za-z0-9._-]+')
    ->name('profile.images.show');

/*
|--------------------------------------------------------------------------
| Authenticated API routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Home
    |--------------------------------------------------------------------------
    */

    Route::get('/home', [
        HomeController::class,
        'currentMonthSubscriptions',
    ]);

    /*
     * Optional old route.
     *
     * Keep this only when an older mobile application is already
     * calling /api/home/current-month-subscriptions.
     */
    Route::get('/home/current-month-subscriptions', [
        HomeController::class,
        'currentMonthSubscriptions',
    ]);

    /*
    |--------------------------------------------------------------------------
    | Profile and profile photo
    |--------------------------------------------------------------------------
    */

    Route::get('/profile', [
        ProfileController::class,
        'show',
    ]);

    Route::get('/profile/photo', [
        ProfileController::class,
        'getPhoto',
    ]);

    Route::post('/profile/photo', [
        ProfileController::class,
        'updatePhoto',
    ]);

    Route::delete('/profile/photo', [
        ProfileController::class,
        'deletePhoto',
    ]);

    /*
     * Legacy delete route for older mobile builds.
     */
    Route::delete('/profile/photo-delete', [
        ProfileController::class,
        'deletePhoto',
    ]);

    Route::post('/logout', [
        AuthController::class,
        'logout',
    ]);

    /*
    |--------------------------------------------------------------------------
    | Addresses
    |--------------------------------------------------------------------------
    */

    Route::get('/addresses', [
        AddressController::class,
        'index',
    ]);

    Route::post('/addresses-create', [
        AddressController::class,
        'store',
    ]);

    Route::get('/addresses/{address}', [
        AddressController::class,
        'show',
    ]);

    Route::patch('/addresses/{address}', [
        AddressController::class,
        'update',
    ]);

    Route::patch('/addresses/{address}/default', [
        AddressController::class,
        'makeDefault',
    ]);

    Route::delete('/addresses/{address}', [
        AddressController::class,
        'destroy',
    ]);

    /*
    |--------------------------------------------------------------------------
    | Customized flower orders
    |--------------------------------------------------------------------------
    */

    Route::post('/custom-orders', [
        CustomOrderController::class,
        'store',
    ]);

    Route::get('/my-orders', [
        CustomOrderController::class,
        'myOrders',
    ]);

    /*
    |--------------------------------------------------------------------------
    | Event bookings and quotations
    |--------------------------------------------------------------------------
    */

    Route::post('/event-bookings', [
        EventBookingController::class,
        'store',
    ]);

    Route::get('/my-quotations', [
        EventBookingController::class,
        'myQuotations',
    ]);

    Route::post('/quotations/{quotation}/accept', [
        EventBookingController::class,
        'acceptQuotation',
    ]);

    /*
    |--------------------------------------------------------------------------
    | Subscriptions
    |--------------------------------------------------------------------------
    */

    Route::post('/subscriptions', [
        SubscriptionController::class,
        'store',
    ]);

    Route::get('/my-subscriptions', [
        SubscriptionController::class,
        'mySubscriptions',
    ]);

    /*
    |--------------------------------------------------------------------------
    | Payments
    |--------------------------------------------------------------------------
    */

    Route::post('/payments/create-order', [
        PaymentController::class,
        'createOrder',
    ]);

    Route::post('/payments/verify', [
        PaymentController::class,
        'verify',
    ]);

    Route::get('/payments/history', [
        PaymentController::class,
        'history',
    ]);
});