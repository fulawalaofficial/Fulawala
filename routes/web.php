<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\CustomOrderController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventBookingController;
use App\Http\Controllers\Admin\FlowerProductController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PoojaPacketController;
use App\Http\Controllers\Admin\QuotationController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\SubscriptionDeliveryController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin/dashboard');

Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.submit');

Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Pooja Packets
    |--------------------------------------------------------------------------
    */
    Route::resource('pooja-packets', PoojaPacketController::class)->except(['show']);

    /*
    |--------------------------------------------------------------------------
    | Flowers
    |--------------------------------------------------------------------------
    */
    Route::resource('flowers', FlowerProductController::class)->except(['show']);

    /*
    |--------------------------------------------------------------------------
    | Custom Orders
    |--------------------------------------------------------------------------
    */
    Route::get('/custom-orders', [CustomOrderController::class, 'index'])
        ->name('custom-orders.index');

    Route::patch('/custom-orders/{customOrder}/status', [CustomOrderController::class, 'updateStatus'])
        ->name('custom-orders.update-status');

    /*
    |--------------------------------------------------------------------------
    | Subscriptions
    |--------------------------------------------------------------------------
    */
    Route::get('/subscriptions', [SubscriptionController::class, 'index'])
        ->name('subscriptions.index');

    Route::get('/subscriptions/create', [SubscriptionController::class, 'create'])
        ->name('subscriptions.create');

    Route::post('/subscriptions', [SubscriptionController::class, 'store'])
        ->name('subscriptions.store');

    /*
    |--------------------------------------------------------------------------
    | Daily Deliveries
    |--------------------------------------------------------------------------
    */
    Route::get('/daily-deliveries', [SubscriptionDeliveryController::class, 'index'])
        ->name('daily-deliveries.index');

    Route::post('/daily-deliveries/generate-today', [SubscriptionDeliveryController::class, 'generateToday'])
        ->name('daily-deliveries.generate-today');

    Route::patch('/daily-deliveries/{delivery}/status', [SubscriptionDeliveryController::class, 'updateStatus'])
        ->name('daily-deliveries.update-status');

    /*
    |--------------------------------------------------------------------------
    | Event Bookings
    |--------------------------------------------------------------------------
    */
    Route::get('/event-bookings', [EventBookingController::class, 'index'])
        ->name('event-bookings.index');

    /*
    |--------------------------------------------------------------------------
    | Quotations
    |--------------------------------------------------------------------------
    */
    Route::get('/quotations', [QuotationController::class, 'index'])
        ->name('quotations.index');

    Route::post('/quotations', [QuotationController::class, 'store'])
        ->name('quotations.store');

    /*
    |--------------------------------------------------------------------------
    | Staff
    |--------------------------------------------------------------------------
    */
    Route::get('/staff', [StaffController::class, 'index'])
        ->name('staff.index');

    Route::post('/staff', [StaffController::class, 'store'])
        ->name('staff.store');

    /*
    |--------------------------------------------------------------------------
    | Payments
    |--------------------------------------------------------------------------
    */
    Route::get('/payments', [PaymentController::class, 'index'])
        ->name('payments.index');

    /*
    |--------------------------------------------------------------------------
    | Customers
    |--------------------------------------------------------------------------
    */
    Route::get('/customers', [CustomerController::class, 'index'])
        ->name('customers.index');

    /*
    |--------------------------------------------------------------------------
    | Reports
    |--------------------------------------------------------------------------
    */
    Route::get('/reports', [ReportController::class, 'index'])
        ->name('reports.index');

    /*
    |--------------------------------------------------------------------------
    | Settings
    |--------------------------------------------------------------------------
    */
    Route::get('/settings', [SettingController::class, 'index'])
        ->name('settings.index');

    Route::post('/settings', [SettingController::class, 'update'])
        ->name('settings.update');
});