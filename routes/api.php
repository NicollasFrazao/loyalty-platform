<?php

declare(strict_types=1);

use App\Http\Controllers\Api\CodeController;
use App\Http\Controllers\Api\FollowingController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\TokenController;
use Illuminate\Support\Facades\Route;

// The customer app's API (L2), described in docs/api/openapi.json; the `api` prefix comes from
// bootstrap/app.php. A customer reaches only their own account: no route names a customer.
Route::pattern('company', '[1-9][0-9]{0,18}');

Route::prefix('v1')->group(static function (): void {
    // Codes cost an SMS: besides the policy's limit per phone, ten requests a minute per address.
    Route::middleware('throttle:10,1')->group(static function (): void {
        Route::post('/auth/code', [CodeController::class, 'store']);
        Route::post('/auth/token', [TokenController::class, 'store']);
    });

    Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(static function (): void {
        Route::delete('/auth/token', [TokenController::class, 'destroy']);
        Route::get('/me', [ProfileController::class, 'show']);
        Route::patch('/me', [ProfileController::class, 'update']);
        Route::delete('/me', [ProfileController::class, 'destroy']);
        Route::get('/me/companies', [FollowingController::class, 'index']);
        Route::put('/me/companies/{company}', [FollowingController::class, 'update']);
        Route::delete('/me/companies/{company}', [FollowingController::class, 'destroy']);
    });
});
