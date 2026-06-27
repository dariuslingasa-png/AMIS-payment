<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GoogleAuthController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PaymentController;

// Root route
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('payment.dashboard');
    }
    return redirect()->route('login');
})->name('welcome');

// Dashboard redirect
Route::get('/dashboard', function () {
    return redirect()->route('payment.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Profile
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::put('/password', [\App\Http\Controllers\Auth\PasswordController::class, 'update'])->name('password.update');
});

// Google OAuth
Route::get('/g-signin', [GoogleAuthController::class, 'redirect'])
    ->middleware('throttle:10,1')
    ->name('auth.google');
Route::match(['get', 'post'], '/g-callback', [GoogleAuthController::class, 'callback'])
    ->middleware('throttle:10,1')
    ->name('auth.google.callback');
Route::get('/auth/unsupported-browser', [GoogleAuthController::class, 'unsupportedBrowser'])
    ->name('auth.unsupported-browser');

// Microsoft OAuth
Route::get('/m-signin', [\App\Http\Controllers\MicrosoftAuthController::class, 'redirect'])
    ->middleware('throttle:10,1')
    ->name('auth.microsoft');
Route::match(['get', 'post'], '/m-callback', [\App\Http\Controllers\MicrosoftAuthController::class, 'callback'])
    ->middleware('throttle:10,1')
    ->name('auth.microsoft.callback');

// Auth routes
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:100,1')->name('register.store');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.store');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// OTP Verification routes
Route::post('/auth/send-otp', [AuthController::class, 'sendOtp'])->middleware('throttle:10,1')->name('auth.send-otp');
Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp'])->middleware('throttle:20,1')->name('auth.verify-otp');

// Email verification — signed link (replaces OTP)
Route::get('/verify-email/notice', [AuthController::class, 'showVerificationNotice'])->name('verification.notice');
Route::get('/verify-email/notice-compat', [AuthController::class, 'showVerificationNotice'])->name('verify.email.notice');
Route::get('/verify-email/status', [AuthController::class, 'checkVerificationStatus'])
    ->middleware('throttle:600,1')
    ->name('verify.email.status');
Route::post('/verify-email/resend', [AuthController::class, 'resendVerificationLink'])->middleware('throttle:100,1')->name('verify.email.resend');
Route::post('/email/verification-notification', [\App\Http\Controllers\Auth\EmailVerificationNotificationController::class, 'store'])
    ->middleware(['auth', 'throttle:10,1'])
    ->name('verification.send');
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'showVerifyConfirm'])
    ->middleware(['throttle:60,1'])
    ->name('verification.verify');

Route::post('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['throttle:60,1'])
    ->name('verification.verify.post');

// Dashboard — accessible to all authenticated and verified users
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/payment/dashboard', [PaymentController::class, 'showDashboard'])->name('payment.dashboard');
    Route::post('/payment/link-student', [PaymentController::class, 'linkStudent'])->name('payment.link-student');
    Route::post('/payment/submit', [PaymentController::class, 'submitPayment'])->name('payment.submit');
    Route::post('/payment/ocr-scan', [PaymentController::class, 'ocrScan'])->name('payment.ocr-scan');
    Route::post('/activity/offline', [AuthController::class, 'setOffline'])->name('activity.offline');
});

if (app()->environment('local')) {
    Route::get('/test-errors/{code}', function ($code) {
        abort((int) $code);
    });
}



