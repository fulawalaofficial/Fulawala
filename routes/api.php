<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomOrderController;
use App\Http\Controllers\Api\EventBookingController;
use App\Http\Controllers\Api\FlowerProductController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PoojaPacketController;
use App\Http\Controllers\Api\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/pooja-packets', [PoojaPacketController::class, 'index']);
Route::get('/pooja-packets/{poojaPacket}', [PoojaPacketController::class, 'show']);
Route::get('/flowers', [FlowerProductController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/subscriptions', [SubscriptionController::class, 'store']);
    Route::get('/my-subscriptions', [SubscriptionController::class, 'mySubscriptions']);

    Route::post('/custom-orders', [CustomOrderController::class, 'store']);
    Route::get('/my-orders', [CustomOrderController::class, 'myOrders']);

    Route::post('/event-bookings', [EventBookingController::class, 'store']);
    Route::get('/my-quotations', [EventBookingController::class, 'myQuotations']);
    Route::post('/quotations/{quotation}/accept', [EventBookingController::class, 'acceptQuotation']);

    Route::post('/payments/create-order', [PaymentController::class, 'createOrder']);
    Route::post('/payments/verify', [PaymentController::class, 'verify']);
    Route::get('/payments/history', [PaymentController::class, 'history']);
});
