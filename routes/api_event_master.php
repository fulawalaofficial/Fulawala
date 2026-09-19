<?php

use App\Http\Controllers\Api\EventMasterController;
use Illuminate\Support\Facades\Route;

/*
| Add these routes inside routes/api.php.
| Public read-only event catalog for the mobile app / website.
*/
Route::get('/event-masters', [EventMasterController::class, 'index']);
Route::get('/event-masters/{eventMaster:slug}', [EventMasterController::class, 'show']);
