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
| Public API Routes
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
])->whereNumber('poojaPacket');

Route::get('/flowers', [
    FlowerProductController::class,
    'index',
]);

/*
|--------------------------------------------------------------------------
| Public Profile Image Route
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
| Authenticated API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function (): void {

    /*
    |--------------------------------------------------------------------------
    | Home
    |--------------------------------------------------------------------------
    */

    Route::get('/home', [
        HomeController::class,
        'currentMonthSubscriptions',
    ]);

    Route::get('/home/current-month-subscriptions', [
        HomeController::class,
        'currentMonthSubscriptions',
    ]);

    /*
    |--------------------------------------------------------------------------
    | Profile
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
     * Compatibility route used by the mobile app.
     */
    Route::delete('/profile/photo-delete', [
        ProfileController::class,
        'deletePhoto',
    ]);

    /*
    |--------------------------------------------------------------------------
    | Authentication / Device
    |--------------------------------------------------------------------------
    */

    Route::post('/logout', [
        AuthController::class,
        'logout',
    ]);

    Route::post('/device-token', [
        AuthController::class,
        'updateDeviceToken',
    ]);

    /*
    |--------------------------------------------------------------------------
    | Customer Addresses
    |--------------------------------------------------------------------------
    |
    | GET     /api/addresses
    | POST    /api/addresses
    | POST    /api/addresses-create      Compatibility alias
    | GET     /api/addresses/{id}
    | PUT     /api/addresses/{id}
    | PATCH   /api/addresses/{id}
    | PATCH   /api/addresses/{id}/default
    | DELETE  /api/addresses/{id}
    |
    */

    Route::get('/addresses', [
        AddressController::class,
        'index',
    ]);

    /*
     * Canonical create endpoint.
     */
    Route::post('/addresses', [
        AddressController::class,
        'store',
    ]);

    /*
     * Compatibility create endpoint currently used by ProfileScreen.js.
     */
    Route::post('/addresses-create', [
        AddressController::class,
        'store',
    ]);

    Route::get('/addresses/{address}', [
        AddressController::class,
        'show',
    ])->whereNumber('address');

    /*
     * Supports both PUT and PATCH from mobile/Postman.
     */
    Route::match(
        ['put', 'patch'],
        '/addresses/{address}',
        [
            AddressController::class,
            'update',
        ],
    )->whereNumber('address');

    Route::patch('/addresses/{address}/default', [
        AddressController::class,
        'makeDefault',
    ])->whereNumber('address');

    Route::delete('/addresses/{address}', [
        AddressController::class,
        'destroy',
    ])->whereNumber('address');

    /*
    |--------------------------------------------------------------------------
    | Customized Flower Orders
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
    | Event Bookings / Quotations
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
    ])->whereNumber('quotation');

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