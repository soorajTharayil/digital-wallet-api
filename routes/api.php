<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['jwt.auth', 'throttle:api'])->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

    Route::get('/wallet', [WalletController::class, 'show']);
    Route::post('/wallet/deposit', [WalletController::class, 'deposit']);
    Route::post('/wallet/withdraw', [WalletController::class, 'withdraw']);
    Route::post('/wallet/transfer', [WalletController::class, 'transfer']);

    Route::get('/transactions', [TransactionController::class, 'index']);
});

Route::fallback(function () {
    return response()->json([
        'status' => 'error',
        'message' => 'Resource not found.',
        'code' => 404,
    ], 404);
});
