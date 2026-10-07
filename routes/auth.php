<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware('guest')->group(function () {
    Volt::route('register', 'pages.auth.register')
        ->name('register');

    Volt::route('forgot-password', 'pages.auth.forgot-password')
        ->name('password.request');

    Volt::route('reset-password/{token}', 'pages.auth.reset-password')
        ->name('password.reset');
});

// Sign-in is open to everyone, so a signed-in user sees a clear notice instead of being redirected.
Volt::route('login', 'pages.auth.login')->name('login');

// The link itself proves ownership of the inbox (it is signed, and checked against the account's email below),
// so confirming it never needs an existing session: it works from any browser or device, not only the one
// used to register.
Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::middleware('auth')->group(function () {
    Volt::route('verify-email', 'pages.auth.verify-email')
        ->name('verification.notice');

    Volt::route('confirm-password', 'pages.auth.confirm-password')
        ->name('password.confirm');
});
