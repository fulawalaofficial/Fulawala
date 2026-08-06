<?php

use App\Http\Controllers\Api\AddressController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Address API routes
|--------------------------------------------------------------------------
|
| Copy these routes inside the existing auth:sanctum route group in
| routes/api.php. Do not add the group twice if it already exists.
|
*/

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/addresses', [
        AddressController::class,
        'index',
    ]);

    Route::post('/addresses', [
        AddressController::class,
        'store',
    ]);

    Route::put('/addresses/{address}', [
        AddressController::class,
        'update',
    ]);

    Route::patch('/addresses/{address}', [
        AddressController::class,
        'update',
    ]);

    Route::delete('/addresses/{address}', [
        AddressController::class,
        'destroy',
    ]);
});
