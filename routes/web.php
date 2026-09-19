<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\CustomOrderController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventBookingController;
use App\Http\Controllers\Admin\EventMasterController;
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

use App\Http\Controllers\Website\AccountDeletionController;
use App\Http\Controllers\Website\ContactController;
use App\Http\Controllers\Website\WebsiteController;

use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Admin Root
|--------------------------------------------------------------------------
*/

Route::redirect('/admin', '/admin/dashboard');


/*
|--------------------------------------------------------------------------
| Admin Authentication
|--------------------------------------------------------------------------
*/

Route::get('/admin/login', [AuthController::class, 'showLogin'])
    ->name('admin.login');

Route::post('/admin/login', [AuthController::class, 'login'])
    ->name('admin.login.submit');


/*
|--------------------------------------------------------------------------
| Protected Admin Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->name('admin.')
    ->middleware('auth')
    ->group(function (): void {

        /*
        |--------------------------------------------------------------------------
        | Logout
        |--------------------------------------------------------------------------
        */

        Route::post('/logout', [AuthController::class, 'logout'])
            ->name('logout');


        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');


        /*
        |--------------------------------------------------------------------------
        | Pooja Packets
        |--------------------------------------------------------------------------
        */

        Route::resource('pooja-packets', PoojaPacketController::class)
            ->except(['show']);


        /*
        |--------------------------------------------------------------------------
        | Flower Products
        |--------------------------------------------------------------------------
        */

        Route::resource('flowers', FlowerProductController::class)
            ->except(['show']);


        /*
        |--------------------------------------------------------------------------
        | Today Deliveries
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/today-deliveries',
            [TodayDeliveryController::class, 'index']
        )->name('today-deliveries.index');

        Route::patch(
            '/today-deliveries/addresses/{address}/coordinates',
            [TodayDeliveryController::class, 'saveCoordinates']
        )->name('today-deliveries.coordinates');


        /*
        |--------------------------------------------------------------------------
        | Custom Orders
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/custom-orders',
            [CustomOrderController::class, 'index']
        )->name('custom-orders.index');

        Route::patch(
            '/custom-orders/{customOrder}/status',
            [CustomOrderController::class, 'updateStatus']
        )->name('custom-orders.update-status');


        /*
        |--------------------------------------------------------------------------
        | Subscriptions
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/subscriptions',
            [SubscriptionController::class, 'index']
        )->name('subscriptions.index');

        Route::get(
            '/subscriptions/create',
            [SubscriptionController::class, 'create']
        )->name('subscriptions.create');

        Route::post(
            '/subscriptions',
            [SubscriptionController::class, 'store']
        )->name('subscriptions.store');


        /*
        |--------------------------------------------------------------------------
        | Daily Subscription Deliveries
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/daily-deliveries',
            [SubscriptionDeliveryController::class, 'index']
        )->name('daily-deliveries.index');

        Route::post(
            '/daily-deliveries/generate-today',
            [SubscriptionDeliveryController::class, 'generateToday']
        )->name('daily-deliveries.generate-today');

        Route::patch(
            '/daily-deliveries/{delivery}/status',
            [SubscriptionDeliveryController::class, 'updateStatus']
        )->name('daily-deliveries.update-status');


        /*
        |--------------------------------------------------------------------------
        | Event Master
        |--------------------------------------------------------------------------
        |
        | Event Master controls reusable events:
        |
        | - Event name
        | - Description
        | - Cover image
        | - Multiple gallery photos
        | - Uploaded videos
        | - YouTube / Vimeo / external video URLs
        | - Event plans
        | - Prices
        | - Status
        |
        */

        Route::resource(
            'event-masters',
            EventMasterController::class
        )
            ->parameters([
                'event-masters' => 'eventMaster',
            ])
            ->except([
                'show',
            ]);


        /*
        |--------------------------------------------------------------------------
        | Event Master Media Delete
        |--------------------------------------------------------------------------
        |
        | Allows individual photo/video/external media to be deleted.
        |
        */

        Route::delete(
            '/event-masters/{eventMaster}/media/{media}',
            [EventMasterController::class, 'destroyMedia']
        )->name('event-masters.media.destroy');


        /*
        |--------------------------------------------------------------------------
        | Event Bookings
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/event-bookings',
            [EventBookingController::class, 'index']
        )->name('event-bookings.index');

        Route::patch(
            '/event-bookings/{eventBooking}/status',
            [EventBookingController::class, 'updateStatus']
        )->name('event-bookings.update-status');


        /*
        |--------------------------------------------------------------------------
        | Quotations
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/quotations',
            [QuotationController::class, 'index']
        )->name('quotations.index');

        Route::post(
            '/quotations',
            [QuotationController::class, 'store']
        )->name('quotations.store');

        Route::patch(
            '/quotations/{quotation}',
            [QuotationController::class, 'update']
        )->name('quotations.update');


        /*
        |--------------------------------------------------------------------------
        | Staff
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/staff',
            [StaffController::class, 'index']
        )->name('staff.index');

        Route::post(
            '/staff',
            [StaffController::class, 'store']
        )->name('staff.store');

        Route::patch(
            '/staff/{staff}',
            [StaffController::class, 'update']
        )->name('staff.update');

        Route::delete(
            '/staff/{staff}',
            [StaffController::class, 'destroy']
        )->name('staff.destroy');


        /*
        |--------------------------------------------------------------------------
        | Payments
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/payments',
            [PaymentController::class, 'index']
        )->name('payments.index');


        /*
        |--------------------------------------------------------------------------
        | Customers
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/customers',
            [CustomerController::class, 'index']
        )->name('customers.index');

        Route::patch(
            '/customers/{customer}/status',
            [CustomerController::class, 'updateStatus']
        )->name('customers.update-status');


        /*
        |--------------------------------------------------------------------------
        | Reports
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/reports',
            [ReportController::class, 'index']
        )->name('reports.index');


        /*
        |--------------------------------------------------------------------------
        | Settings
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/settings',
            [SettingController::class, 'index']
        )->name('settings.index');

        Route::post(
            '/settings',
            [SettingController::class, 'update']
        )->name('settings.update');
    });


/*
|--------------------------------------------------------------------------
| Public Website Routes
|--------------------------------------------------------------------------
*/

Route::name('website.')
    ->group(function (): void {

        /*
        |--------------------------------------------------------------------------
        | Home
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/',
            [WebsiteController::class, 'home']
        )->name('home');


        /*
        |--------------------------------------------------------------------------
        | About
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/about',
            [WebsiteController::class, 'about']
        )->name('about');


        /*
        |--------------------------------------------------------------------------
        | Flowers
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/flowers',
            [WebsiteController::class, 'flowers']
        )->name('flowers');


        /*
        |--------------------------------------------------------------------------
        | Pooja Packets
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/pooja-packets',
            [WebsiteController::class, 'poojaPackets']
        )->name('pooja-packets');


        /*
        |--------------------------------------------------------------------------
        | Subscriptions
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/subscriptions',
            [WebsiteController::class, 'subscriptions']
        )->name('subscriptions');


        /*
        |--------------------------------------------------------------------------
        | Event Decoration
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/event-decoration',
            [WebsiteController::class, 'events']
        )->name('events');


        /*
        |--------------------------------------------------------------------------
        | Gallery
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/gallery',
            [WebsiteController::class, 'gallery']
        )->name('gallery');


        /*
        |--------------------------------------------------------------------------
        | Contact
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/contact',
            [WebsiteController::class, 'contact']
        )->name('contact');

        Route::post(
            '/contact',
            [ContactController::class, 'store']
        )
            ->middleware('throttle:5,1')
            ->name('contact.submit');


        /*
        |--------------------------------------------------------------------------
        | Privacy Policy
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/privacy',
            [WebsiteController::class, 'privacy']
        )->name('privacy');


        /*
        |--------------------------------------------------------------------------
        | Terms & Conditions
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/terms-and-conditions',
            [WebsiteController::class, 'terms']
        )->name('terms');


        /*
        |--------------------------------------------------------------------------
        | Account Deletion
        |--------------------------------------------------------------------------
        |
        | This URL can be provided to Google Play Console as Fulawala's
        | account deletion page.
        |
        */

        Route::get(
            '/delete-account',
            [AccountDeletionController::class, 'show']
        )->name('account-delete.form');

        Route::delete(
            '/delete-account',
            [AccountDeletionController::class, 'destroy']
        )
            ->middleware('throttle:3,1')
            ->name('account-delete.destroy');
    });