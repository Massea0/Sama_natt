<?php

use App\Http\Controllers\Api\V1;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
*/

// Health check (public)
Route::get('/v1/health', fn() => response()->json(['status' => 'ok', 'version' => 'v1']));

// Protected routes
Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {
    // User profile
    Route::get('/me', [V1\UserController::class, 'show']);
    Route::put('/me', [V1\UserController::class, 'update']);
    Route::get('/me/guilds', [V1\UserController::class, 'guilds']);

    // Guild-scoped routes
    Route::prefix('guilds/{guild}')
        ->middleware(['api.tenant'])
        ->group(function () {
            Route::get('/', [V1\GuildController::class, 'show']);
            Route::get('/members', [V1\GuildController::class, 'members']);
            Route::get('/rounds', [V1\RoundController::class, 'index']);

            // Round-scoped routes
            Route::prefix('rounds/{round}')->group(function () {
                Route::get('/', [V1\RoundController::class, 'show']);
                Route::get('/sessions', [V1\SessionController::class, 'index']);

                // Session-scoped routes
                Route::prefix('sessions/{session}')->group(function () {
                    Route::get('/', [V1\SessionController::class, 'show']);
                    Route::get('/beneficiary', [V1\SessionController::class, 'beneficiary']);
                });
            });
        });
});
