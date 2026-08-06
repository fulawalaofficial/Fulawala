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
use App\Http\Controllers\Admin\TodayDeliveryController;
use App\Http\Controllers\Website\ContactController;
use App\Http\Controllers\Website\WebsiteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin authentication
|--------------------------------------------------------------------------
*/
Route::redirect('/admin', '/admin/dashboard');

Route::get('/admin/login', [AuthController::class, 'showLogin'])
    ->name('admin.login');

Route::post('/admin/login', [AuthController::class, 'login'])
    ->name('admin.login.submit');

Route::prefix('admin')
    ->name('admin.')
    ->middleware('auth')
    ->group(function (): void {
        Route::post('/logout', [AuthController::class, 'logout'])
            ->name('logout');

        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        Route::resource('pooja-packets', PoojaPacketController::class)
            ->except(['show']);

        Route::resource('flowers', FlowerProductController::class)
            ->except(['show']);

        /* Today delivery operations page. */
        Route::get('/today-deliveries', [TodayDeliveryController::class, 'index'])
            ->name('today-deliveries.index');

        Route::patch(
            '/today-deliveries/addresses/{address}/coordinates',
            [TodayDeliveryController::class, 'saveCoordinates']
        )->name('today-deliveries.coordinates');

        /* Custom orders. */
        Route::get('/custom-orders', [CustomOrderController::class, 'index'])
            ->name('custom-orders.index');

        Route::patch(
            '/custom-orders/{customOrder}/status',
            [CustomOrderController::class, 'updateStatus']
        )->name('custom-orders.update-status');

        /* Subscriptions. */
        Route::get('/subscriptions', [SubscriptionController::class, 'index'])
            ->name('subscriptions.index');

        Route::get('/subscriptions/create', [SubscriptionController::class, 'create'])
            ->name('subscriptions.create');

        Route::post('/subscriptions', [SubscriptionController::class, 'store'])
            ->name('subscriptions.store');

        /* Daily subscription deliveries. */
        Route::get('/daily-deliveries', [SubscriptionDeliveryController::class, 'index'])
            ->name('daily-deliveries.index');

        Route::post(
            '/daily-deliveries/generate-today',
            [SubscriptionDeliveryController::class, 'generateToday']
        )->name('daily-deliveries.generate-today');

        Route::patch(
            '/daily-deliveries/{delivery}/status',
            [SubscriptionDeliveryController::class, 'updateStatus']
        )->name('daily-deliveries.update-status');

        /* Event bookings. */
        Route::get('/event-bookings', [EventBookingController::class, 'index'])
            ->name('event-bookings.index');

        Route::patch(
            '/event-bookings/{eventBooking}/status',
            [EventBookingController::class, 'updateStatus']
        )->name('event-bookings.update-status');

        /* Quotations. */
        Route::get('/quotations', [QuotationController::class, 'index'])
            ->name('quotations.index');

        Route::post('/quotations', [QuotationController::class, 'store'])
            ->name('quotations.store');

        Route::patch('/quotations/{quotation}', [QuotationController::class, 'update'])
            ->name('quotations.update');

        /* Staff. */
        Route::get('/staff', [StaffController::class, 'index'])
            ->name('staff.index');

        Route::post('/staff', [StaffController::class, 'store'])
            ->name('staff.store');

        Route::patch('/staff/{staff}', [StaffController::class, 'update'])
            ->name('staff.update');

        Route::delete('/staff/{staff}', [StaffController::class, 'destroy'])
            ->name('staff.destroy');

        Route::get('/payments', [PaymentController::class, 'index'])
            ->name('payments.index');

        Route::get('/customers', [CustomerController::class, 'index'])
            ->name('customers.index');

        Route::patch(
            '/customers/{customer}/status',
            [CustomerController::class, 'updateStatus']
        )->name('customers.update-status');

        Route::get('/reports', [ReportController::class, 'index'])
            ->name('reports.index');

        Route::get('/settings', [SettingController::class, 'index'])
            ->name('settings.index');

        Route::post('/settings', [SettingController::class, 'update'])
            ->name('settings.update');
    });

/*
|--------------------------------------------------------------------------
| Public website
|--------------------------------------------------------------------------
*/
Route::name('website.')->group(function (): void {
    Route::get('/', [WebsiteController::class, 'home'])->name('home');
    Route::get('/about', [WebsiteController::class, 'about'])->name('about');
    Route::get('/flowers', [WebsiteController::class, 'flowers'])->name('flowers');
    Route::get('/pooja-packets', [WebsiteController::class, 'poojaPackets'])->name('pooja-packets');
    Route::get('/subscriptions', [WebsiteController::class, 'subscriptions'])->name('subscriptions');
    Route::get('/event-decoration', [WebsiteController::class, 'events'])->name('events');
    Route::get('/gallery', [WebsiteController::class, 'gallery'])->name('gallery');
    Route::get('/contact', [WebsiteController::class, 'contact'])->name('contact');
    Route::post('/contact', [ContactController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('contact.submit');
    Route::get('/privacy-policy', [WebsiteController::class, 'privacy'])->name('privacy');
    Route::get('/terms-and-conditions', [WebsiteController::class, 'terms'])->name('terms');
});
