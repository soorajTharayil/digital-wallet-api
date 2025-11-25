<?php

use App\Http\Controllers\Web\AuthController as WebAuthController;
use App\Http\Controllers\Web\WalletController as WebWalletController;
use App\Http\Controllers\Web\TransactionController as WebTransactionController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

// Public routes
Route::get('/register', [WebAuthController::class, 'showRegister'])->name('register.show');
Route::post('/register', [WebAuthController::class, 'register'])->name('register');
Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login.show');
Route::post('/login', [WebAuthController::class, 'login'])->name('login');

// Protected routes (require authentication)
Route::middleware(['web.auth'])->group(function () {
    Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');
    Route::post('/refresh', [WebAuthController::class, 'refresh'])->name('refresh');
    
    Route::get('/dashboard', [WebWalletController::class, 'dashboard'])->name('dashboard');
    Route::get('/wallet', [WebWalletController::class, 'show'])->name('wallet.show');
    
    Route::get('/wallet/deposit', [WebWalletController::class, 'showDeposit'])->name('wallet.deposit.show');
    Route::post('/wallet/deposit', [WebWalletController::class, 'deposit'])->name('wallet.deposit');
    
    Route::get('/wallet/withdraw', [WebWalletController::class, 'showWithdraw'])->name('wallet.withdraw.show');
    Route::post('/wallet/withdraw', [WebWalletController::class, 'withdraw'])->name('wallet.withdraw');
    
    Route::get('/wallet/transfer', [WebWalletController::class, 'showTransfer'])->name('wallet.transfer.show');
    Route::post('/wallet/transfer', [WebWalletController::class, 'transfer'])->name('wallet.transfer');
    
    Route::get('/transactions', [WebTransactionController::class, 'index'])->name('transactions.index');
});

// Redirect root to dashboard or login
Route::get('/', function () {
    return session('jwt_token') ? redirect()->route('dashboard') : redirect()->route('login.show');
})->name('home');

// Temporary migration route (REMOVE AFTER USE)
Route::get('/run-migrate', function () {
    Artisan::call('migrate', ['--force' => true]);
    return "Migrations executed!";
});

