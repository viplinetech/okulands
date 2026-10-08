<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

use App\Http\Controllers\Admin;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\Realtor;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\TwoFactorController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

/*
|--------------------------------------------------------------------------
| Public website
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');

Route::get('/properties', [PropertyController::class, 'index'])->name('properties.index');
Route::get('/properties/{slug}', [PropertyController::class, 'show'])->name('properties.show');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/services', [PageController::class, 'services'])->name('services');
Route::get('/gallery', [PageController::class, 'gallery'])->name('gallery');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::post('/contact', [LeadController::class, 'store'])->middleware('throttle:8,1')->name('contact.store');

// Help Center (FAQ knowledge base)
Route::get('/faqs', [FaqController::class, 'index'])->name('faq');

// Legal pages (fixed wording, see App\Support\LegalDefaults)
Route::get('/{page}', [PageController::class, 'legal'])->whereIn('page', ['privacy-policy', 'terms'])->name('legal');
Route::post('/faqs/{faqItem}/feedback', [FaqController::class, 'feedback'])->middleware('throttle:30,1')->name('faq.feedback');
Route::post('/faqs/ask', [FaqController::class, 'ask'])->middleware('throttle:8,1')->name('faq.ask');

// Realtor referral links, e.g. /ref/john-a1b2  (optionally  ?to=/properties/some-plot)
Route::get('/ref/{code}', [ReferralController::class, 'track'])->where('code', '[A-Za-z0-9_-]+')->middleware('throttle:60,1')->name('referral');

/*
|--------------------------------------------------------------------------
| Signed-in users (login / register / password screens live in routes/auth.php)
|--------------------------------------------------------------------------
*/
// The private admin sign-in. Not linked anywhere on the public site; only admins can pass it.
Volt::route('adminbackend', 'pages.auth.admin-login')->middleware(['nostore'])->name('admin.login');

Route::middleware(['auth', 'active', 'nostore'])->group(function () {
    Route::post('logout', LogoutController::class)->name('logout');

    // Second step of sign-in for accounts with two-factor enabled (a Volt page, styled like the sign-in screen).
    Volt::route('two-factor-challenge', 'pages.auth.two-factor-challenge')->name('two-factor.challenge');
});

Route::middleware(['auth', 'active', 'twofactor', 'nostore'])->group(function () {
    // Shared account actions (used by both the realtor and admin security pages)
    Route::post('account/password', [PasswordController::class, 'update'])->middleware('throttle:6,1')->name('account.password');
    Route::post('account/two-factor/start', [TwoFactorController::class, 'start'])->name('two-factor.start');
    Route::post('account/two-factor/confirm', [TwoFactorController::class, 'confirm'])->middleware('throttle:10,1')->name('two-factor.confirm');
    Route::post('account/two-factor/recovery', [TwoFactorController::class, 'regenerate'])->middleware('throttle:6,1')->name('two-factor.recovery');
    Route::delete('account/two-factor', [TwoFactorController::class, 'disable'])->middleware('throttle:6,1')->name('two-factor.disable');

    // Old Breeze entry point: send everyone to their own area.
    Route::get('dashboard', fn () => redirect()->route(auth()->user()->homeRoute()))->name('dashboard');

    /*
    |----------------------------------------------------------------------
    | Realtor app (realtors and downliners)
    |----------------------------------------------------------------------
    */
    Route::prefix('realtor')->name('realtor.')->middleware(['role:affiliate', 'verified'])->group(function () {
        Route::get('/', Realtor\DashboardController::class)->name('dashboard');
        Route::get('welcome', fn () => view('realtor.welcome'))->name('welcome');
        Route::get('leads', [Realtor\LeadController::class, 'index'])->name('leads');
        Route::get('team', [Realtor\DownlineController::class, 'index'])->name('downline');
        Route::get('earnings', [Realtor\EarningsController::class, 'index'])->name('earnings');
        Route::post('withdrawals', [Realtor\WithdrawalController::class, 'store'])->middleware('throttle:10,1')->name('withdrawals.store');
        Route::get('share', [Realtor\ShareController::class, 'index'])->name('share');
        Route::get('listings', [Realtor\ListingController::class, 'index'])->name('properties');
        Route::get('alerts', [NotificationController::class, 'index'])->name('notifications');
        Route::post('alerts/read', [NotificationController::class, 'markAllRead'])->name('notifications.read');
        Route::get('profile', [Realtor\ProfileController::class, 'edit'])->name('profile');
        Route::put('profile', [Realtor\ProfileController::class, 'update'])->name('profile.update');
        Route::get('security', [Realtor\SecurityController::class, 'index'])->name('security');
    });

    /*
    |----------------------------------------------------------------------
    | Admin, all under /adminbackend (role:admin, 30-minute idle timeout, two-factor mandatory)
    |----------------------------------------------------------------------
    */
    Route::prefix('adminbackend')->name('admin.')->middleware(['role:admin', 'idle:30'])->group(function () {
        Route::get('dashboard', Admin\DashboardController::class)->name('dashboard');
        // "Confirm it is you": fresh 2FA code before sensitive actions (see RequireStepUp).
        Route::get('confirm', [Admin\StepUpController::class, 'show'])->name('confirm');
        Route::post('confirm', [Admin\StepUpController::class, 'verify'])->middleware('throttle:10,1')->name('confirm.verify');

        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications');
        Route::post('notifications/read', [NotificationController::class, 'markAllRead'])->name('notifications.read');
        Route::get('security', [Admin\SecurityController::class, 'index'])->name('security');
        Route::get('activity', [Admin\ActivityController::class, 'index'])->name('activity');

        // Sales pipeline
        Route::get('leads', [Admin\LeadController::class, 'index'])->name('leads.index');
        Route::get('leads/{lead}', [Admin\LeadController::class, 'show'])->name('leads.show');
        Route::put('leads/{lead}', [Admin\LeadController::class, 'update'])->name('leads.update');
        Route::delete('leads/{lead}', [Admin\LeadController::class, 'destroy'])->name('leads.destroy');

        Route::get('sales', [Admin\SaleController::class, 'index'])->name('sales.index');
        Route::get('sales/create', [Admin\SaleController::class, 'create'])->middleware('stepup')->name('sales.create');
        Route::post('sales', [Admin\SaleController::class, 'store'])->middleware('stepup')->name('sales.store');
        Route::get('sales/{sale}', [Admin\SaleController::class, 'show'])->name('sales.show');
        Route::post('sales/{sale}/approve', [Admin\SaleController::class, 'approve'])->middleware('stepup')->name('sales.approve');
        Route::post('sales/{sale}/cancel', [Admin\SaleController::class, 'cancel'])->middleware('stepup')->name('sales.cancel');

        Route::get('commissions', [Admin\CommissionController::class, 'index'])->name('commissions.index');
        Route::post('commissions/{commission}/pay', [Admin\CommissionController::class, 'pay'])->middleware('stepup')->name('commissions.pay');
        Route::put('commissions/rates', [Admin\CommissionController::class, 'updateRates'])->middleware('stepup')->name('commissions.rates');

        Route::get('withdrawals', [Admin\WithdrawalController::class, 'index'])->name('withdrawals.index');
        Route::post('withdrawals/{withdrawal}/pay', [Admin\WithdrawalController::class, 'pay'])->middleware('stepup')->name('withdrawals.pay');
        Route::post('withdrawals/{withdrawal}/reject', [Admin\WithdrawalController::class, 'reject'])->middleware('stepup')->name('withdrawals.reject');

        Route::get('realtors', [Admin\RealtorController::class, 'index'])->name('realtors.index');
        Route::get('realtors/create', [Admin\RealtorController::class, 'create'])->middleware('stepup')->name('realtors.create');
        Route::post('realtors', [Admin\RealtorController::class, 'store'])->middleware('stepup')->name('realtors.store');
        Route::get('realtors/{realtor}', [Admin\RealtorController::class, 'show'])->name('realtors.show');
        Route::put('realtors/{realtor}', [Admin\RealtorController::class, 'update'])->middleware('stepup')->name('realtors.update');
        Route::delete('realtors/{realtor}/two-factor', [Admin\RealtorController::class, 'resetTwoFactor'])->middleware('stepup')->name('realtors.two-factor');
        Route::post('realtors/{realtor}/status', [Admin\RealtorController::class, 'status'])->middleware('stepup')->name('realtors.status');

        // Reports
        Route::get('reports', [Admin\ReportController::class, 'index'])->name('reports');
        Route::get('reports/export/{type}', [Admin\ReportController::class, 'export'])->middleware('stepup')->name('reports.export');

        // Website content
        Route::resource('properties', Admin\PropertyController::class)->except('show');
        Route::resource('services', Admin\ServiceController::class)->except('show');
        Route::post('gallery/bulk', [Admin\GalleryController::class, 'bulk'])->name('gallery.bulk');
        Route::resource('gallery', Admin\GalleryController::class)->except('show')->parameters(['gallery' => 'item']);
        Route::resource('posts', Admin\PostController::class)->except('show');
        Route::resource('faqs', Admin\FaqController::class)->except('show');
        Route::get('testimonials', [Admin\TestimonialController::class, 'index'])->name('testimonials.index');
        Route::post('testimonials', [Admin\TestimonialController::class, 'store'])->name('testimonials.store');
        Route::put('testimonials/{testimonial}', [Admin\TestimonialController::class, 'update'])->name('testimonials.update');
        Route::delete('testimonials/{testimonial}', [Admin\TestimonialController::class, 'destroy'])->name('testimonials.destroy');

        Route::post('ai/property-description', [Admin\AiController::class, 'propertyDescription'])->middleware('throttle:20,1')->name('ai.property-description');
        Route::post('ai/blog-post', [Admin\AiController::class, 'blogPost'])->middleware('throttle:20,1')->name('ai.blog-post');
        Route::post('ai/blog-post-full', [Admin\AiController::class, 'blogPostFull'])->middleware('throttle:20,1')->name('ai.blog-post-full');


        Route::get('settings', [Admin\SettingsController::class, 'edit'])->name('settings');
        Route::put('settings', [Admin\SettingsController::class, 'update'])->name('settings.update');
    });
});

require __DIR__.'/auth.php';
