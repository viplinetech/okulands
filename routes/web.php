<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

use App\Http\Controllers\HomeController;
use App\Http\Controllers\LeadController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/contact', [LeadController::class, 'store'])->name('contact.store');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
