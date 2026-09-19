<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomOrderController;
use App\Http\Controllers\Api\EventBookingController;
use App\Http\Controllers\Api\EventMasterController;
use App\Http\Controllers\Api\EventQuotationPaymentController;
use App\Http\Controllers\Api\FlowerProductController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PoojaPacketController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RazorpayWebhookController;
use App\Http\Controllers\Api\SubscriptionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public authentication routes
|--------------------------------------------------------------------------
*/

Route::post('/register', [AuthController::class, 'register'])
    ->middleware('throttle:3,1');

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1');

Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])
    ->middleware('throttle:3,1');

Route::post('/reset-password', [AuthController::class, 'resetPassword'])
    ->middleware('throttle:5,1');

/*
|--------------------------------------------------------------------------
| Razorpay Webhook
|--------------------------------------------------------------------------
|
| Public because Razorpay servers call it.
| Your RazorpayWebhookController must verify X-Razorpay-Signature.
|
*/

Route::post(
    '/razorpay/webhook',
    [RazorpayWebhookController::class, 'handle']
)->middleware('throttle:120,1');

/*
|--------------------------------------------------------------------------
| Public product routes
|--------------------------------------------------------------------------
*/

Route::get(
    '/flower-package',
    [PoojaPacketController::class, 'index']
);

Route::get(
    '/pooja-packets/{poojaPacket}',
    [PoojaPacketController::class, 'show']
)->whereNumber('poojaPacket');

Route::get(
    '/flowers',
    [FlowerProductController::class, 'index']
);

Route::get(
    '/profile-images/{filename}',
    [ProfileController::class, 'showPhotoFile']
)
    ->where('filename', '[A-Za-z0-9._-]+')
    ->name('profile.images.show');

/*
|--------------------------------------------------------------------------
| Authenticated Application Routes
|--------------------------------------------------------------------------
|
| IMPORTANT:
| This is intentionally ONE auth:sanctum group.
| Your previous api.php had duplicate event/address/quotation routes.
|
*/

Route::middleware('auth:sanctum')->group(function (): void {

    /*
    |--------------------------------------------------------------------------
    | Home
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/home',
        [HomeController::class, 'currentMonthSubscriptions']
    );

    Route::get(
        '/home/current-month-subscriptions',
        [HomeController::class, 'currentMonthSubscriptions']
    );

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/profile',
        [ProfileController::class, 'show']
    );

    Route::get(
        '/profile/photo',
        [ProfileController::class, 'getPhoto']
    );

    Route::post(
        '/profile/photo',
        [ProfileController::class, 'updatePhoto']
    );

    Route::delete(
        '/profile/photo',
        [ProfileController::class, 'deletePhoto']
    );

    Route::delete(
        '/profile/photo-delete',
        [ProfileController::class, 'deletePhoto']
    );

    /*
    |--------------------------------------------------------------------------
    | Authentication / Device
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/logout',
        [AuthController::class, 'logout']
    );

    Route::post(
        '/device-token',
        [AuthController::class, 'updateDeviceToken']
    );

    /*
    |--------------------------------------------------------------------------
    | Addresses
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/addresses',
        [AddressController::class, 'index']
    );

    Route::post(
        '/addresses',
        [AddressController::class, 'store']
    );

    // Legacy alias retained for existing app screens.
    Route::post(
        '/addresses-create',
        [AddressController::class, 'store']
    );

    Route::get(
        '/addresses/{address}',
        [AddressController::class, 'show']
    )->whereNumber('address');

    Route::match(
        ['put', 'patch'],
        '/addresses/{address}',
        [AddressController::class, 'update']
    )->whereNumber('address');

    Route::patch(
        '/addresses/{address}/default',
        [AddressController::class, 'makeDefault']
    )->whereNumber('address');

    Route::delete(
        '/addresses/{address}',
        [AddressController::class, 'destroy']
    )->whereNumber('address');

    /*
    |--------------------------------------------------------------------------
    | Custom Orders
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/custom-orders',
        [CustomOrderController::class, 'store']
    );

    Route::get(
        '/my-orders',
        [CustomOrderController::class, 'myOrders']
    );

    /*
    |--------------------------------------------------------------------------
    | Event Master
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/event-masters',
        [EventMasterController::class, 'index']
    );

    Route::get(
        '/event-masters/{eventMaster}',
        [EventMasterController::class, 'show']
    )->whereNumber('eventMaster');

    /*
    |--------------------------------------------------------------------------
    | Event Booking
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/event-bookings',
        [EventBookingController::class, 'store']
    );

    Route::get(
        '/my-quotations',
        [EventBookingController::class, 'myQuotations']
    );

    /*
    |--------------------------------------------------------------------------
    | Event Quotation Razorpay
    |--------------------------------------------------------------------------
    |
    | Old MOCK endpoint removed:
    |
    | POST /quotations/{quotation}/accept
    |
    | Advance/full payment now uses real Razorpay order + verification.
    |
    */

    Route::post(
        '/quotations/{quotation}/payment-order',
        [EventQuotationPaymentController::class, 'createOrder']
    )->whereNumber('quotation');

    Route::post(
        '/quotations/{quotation}/payments/verify',
        [EventQuotationPaymentController::class, 'verify']
    )->whereNumber('quotation');

    /*
    |--------------------------------------------------------------------------
    | Subscriptions
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/subscriptions',
        [SubscriptionController::class, 'store']
    );

    Route::get(
        '/my-subscriptions',
        [SubscriptionController::class, 'mySubscriptions']
    );

    /*
    |--------------------------------------------------------------------------
    | Existing Subscription / General Razorpay Payment Endpoints
    |--------------------------------------------------------------------------
    |
    | Kept unchanged so your current subscription payment flow continues working.
    |
    */

    Route::post(
        '/payments/create-order',
        [PaymentController::class, 'createOrder']
    );

    Route::post(
        '/payments/verify',
        [PaymentController::class, 'verify']
    );

    Route::get(
        '/payments/history',
        [PaymentController::class, 'history']
    );
});
