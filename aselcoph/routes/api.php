<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\DeviceTokenController;
use App\Http\Controllers\Api\V1\LedgerController;
use App\Http\Controllers\Api\V1\MembershipController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\NotificationPreferenceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Legacy API routes
|--------------------------------------------------------------------------
|
| Deprecated in favor of /api/v1/auth/user. Kept for backward compatibility.
|
*/
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| Mobile API v1 — token-based auth (Sanctum personal access tokens)
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])
            ->middleware('throttle:api-register');

        Route::post('/login', [AuthController::class, 'login'])
            ->middleware('throttle:api-login');

        // Public resend: login does not issue tokens to unverified users.
        Route::post('/email/resend', [AuthController::class, 'resendVerification'])
            ->middleware('throttle:6,1');

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/logout-all', [AuthController::class, 'logoutAll']);

            Route::get('/user', [AuthController::class, 'user'])
                ->middleware('verified');
        });
    });

    Route::prefix('membership')->middleware(['auth:sanctum', 'verified'])->group(function () {
        Route::get('/status', [MembershipController::class, 'status']);
        Route::get('/privacy', [MembershipController::class, 'privacy']);
        Route::get('/account-links', [MembershipController::class, 'index']);
        Route::post('/account-links', [MembershipController::class, 'store'])
            ->middleware('throttle:api-membership');
        Route::get('/linked-accounts', [MembershipController::class, 'linkedAccounts']);
    });

    Route::prefix('dashboard')->middleware(['auth:sanctum', 'verified'])->group(function () {
        Route::get('/summary', [DashboardController::class, 'summary']);
    });

    Route::prefix('ledger')->middleware(['auth:sanctum', 'verified'])->group(function () {
        Route::get('/', [LedgerController::class, 'show'])
            ->middleware('throttle:api-ledger');
    });

    Route::middleware(['auth:sanctum', 'verified'])->group(function () {
        Route::post('/devices', [DeviceTokenController::class, 'store']);
        Route::delete('/devices', [DeviceTokenController::class, 'destroy']);

        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])
            ->whereNumber('id');
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);

        Route::get('/notification-preferences', [NotificationPreferenceController::class, 'show']);
        Route::put('/notification-preferences', [NotificationPreferenceController::class, 'update']);
    });
});
