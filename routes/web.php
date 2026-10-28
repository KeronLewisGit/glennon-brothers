<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CareerController;
use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\FrameController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\ThanksController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/plants', [PageController::class, 'plants'])->name('plants.index');
Route::get('/plants/{plant}', [PageController::class, 'plant'])->name('plants.show')->whereIn('plant', ['longford', 'fermoy', 'windymains', 'troon']);
Route::get('/products', [PageController::class, 'products'])->name('products');
Route::get('/timber-frame', [PageController::class, 'timberFrame'])->name('frame.index');
Route::get('/timber-frame/enquire', [FrameController::class, 'create'])->name('frame.create');
Route::get('/sustainability', [PageController::class, 'sustainability'])->name('sustainability');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/news', [PageController::class, 'news'])->name('news');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');

Route::get('/trade-quote', [QuoteController::class, 'create'])->name('quotes.create');
Route::get('/haulier-booking', [BookingController::class, 'create'])->name('bookings.create');
Route::get('/careers', [CareerController::class, 'index'])->name('careers.index');
Route::get('/careers/apply', [CareerController::class, 'create'])->name('careers.apply');
Route::get('/careers/{job}', [CareerController::class, 'show'])->name('careers.show');

Route::middleware('throttle:forms')->group(function () {
    Route::post('/trade-quote', [QuoteController::class, 'store'])->name('quotes.store');
    Route::post('/timber-frame/enquire', [FrameController::class, 'store'])->name('frame.store');
    Route::post('/haulier-booking', [BookingController::class, 'store'])->name('bookings.store');
    Route::post('/careers/apply', [CareerController::class, 'store'])->name('careers.store');
    Route::post('/contact', [EnquiryController::class, 'store'])->name('contact.store');
});
Route::get('/thanks', ThanksController::class)->name('thanks');

// Signed, expiring link from the customer's confirmation email: send DTE more drawings.
Route::middleware('signed')->group(function () {
    Route::get('/timber-frame/drawings/{enquiry}/{key}', [FrameController::class, 'drawings'])->name('frame.drawings');
    Route::post('/timber-frame/drawings/{enquiry}/{key}', [FrameController::class, 'storeDrawings'])->middleware('throttle:forms')->name('frame.drawings.store');
});

Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/photo-credits', [PageController::class, 'credits'])->name('credits');
Route::get('/sitemap', [PageController::class, 'sitemapPage'])->name('sitemap.page');
Route::get('/sitemap.xml', [PageController::class, 'sitemap'])->name('sitemap');

// Pages on the current static site (aboutus.html, contactus.html, ...) that may still be bookmarked.
foreach ((array) config('glennon.legacy_redirects', []) as $from => $to) {
    Route::permanentRedirect($from, $to);
}

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'show'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'active'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', Admin\DashboardController::class)->name('dashboard');

    Route::middleware('area:quotes')->group(function () {
        Route::get('/quotes', [Admin\QuoteController::class, 'index'])->name('quotes.index');
        Route::get('/quotes/export', [Admin\QuoteController::class, 'export'])->name('quotes.export');
        Route::get('/quotes/{quote}', [Admin\QuoteController::class, 'show'])->name('quotes.show');
        Route::patch('/quotes/{quote}', [Admin\QuoteController::class, 'update'])->name('quotes.update');
        Route::post('/quotes/{quote}/notes', [Admin\QuoteController::class, 'note'])->name('quotes.note');
        Route::get('/quotes/{quote}/schedule', [Admin\QuoteController::class, 'schedule'])->name('quotes.schedule');
    });

    Route::middleware('area:frame')->group(function () {
        Route::get('/timber-frame', [Admin\FrameEnquiryController::class, 'index'])->name('frame.index');
        Route::get('/timber-frame/export', [Admin\FrameEnquiryController::class, 'export'])->name('frame.export');
        Route::get('/timber-frame/{enquiry}', [Admin\FrameEnquiryController::class, 'show'])->name('frame.show');
        Route::patch('/timber-frame/{enquiry}', [Admin\FrameEnquiryController::class, 'update'])->name('frame.update');
        Route::post('/timber-frame/{enquiry}/notes', [Admin\FrameEnquiryController::class, 'note'])->name('frame.note');
        Route::post('/timber-frame/{enquiry}/revoke', [Admin\FrameEnquiryController::class, 'revoke'])->name('frame.revoke');
        Route::get('/timber-frame/{enquiry}/drawings/{drawing}', [Admin\FrameEnquiryController::class, 'drawing'])->name('frame.drawing');
    });

    Route::middleware('area:careers')->group(function () {
        Route::get('/jobs', [Admin\JobPostingController::class, 'index'])->name('jobs.index');
        Route::get('/jobs/create', [Admin\JobPostingController::class, 'create'])->name('jobs.create');
        Route::post('/jobs', [Admin\JobPostingController::class, 'store'])->name('jobs.store');
        Route::get('/jobs/{job}/edit', [Admin\JobPostingController::class, 'edit'])->name('jobs.edit');
        Route::put('/jobs/{job}', [Admin\JobPostingController::class, 'update'])->name('jobs.update');
        Route::get('/applications', [Admin\ApplicationController::class, 'index'])->name('applications.index');
        Route::get('/applications/export', [Admin\ApplicationController::class, 'export'])->name('applications.export');
        Route::get('/applications/{application}', [Admin\ApplicationController::class, 'show'])->name('applications.show');
        Route::patch('/applications/{application}', [Admin\ApplicationController::class, 'update'])->name('applications.update');
        Route::post('/applications/{application}/notes', [Admin\ApplicationController::class, 'note'])->name('applications.note');
        Route::get('/applications/{application}/cv', [Admin\ApplicationController::class, 'cv'])->name('applications.cv');
    });

    Route::middleware('area:deliveries')->group(function () {
        Route::get('/bookings', [Admin\BookingController::class, 'index'])->name('bookings.index');
        Route::get('/bookings/{booking}', [Admin\BookingController::class, 'show'])->name('bookings.show');
        Route::patch('/bookings/{booking}', [Admin\BookingController::class, 'update'])->name('bookings.update');
        Route::post('/bookings/{booking}/notes', [Admin\BookingController::class, 'note'])->name('bookings.note');
    });

    Route::middleware('area:enquiries')->group(function () {
        Route::get('/enquiries', [Admin\EnquiryController::class, 'index'])->name('enquiries.index');
        Route::get('/enquiries/{enquiry}', [Admin\EnquiryController::class, 'show'])->name('enquiries.show');
        Route::patch('/enquiries/{enquiry}', [Admin\EnquiryController::class, 'update'])->name('enquiries.update');
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('/users', [Admin\UserController::class, 'index'])->name('users.index');
        Route::post('/users', [Admin\UserController::class, 'store'])->name('users.store');
        Route::patch('/users/{user}', [Admin\UserController::class, 'update'])->name('users.update');
    });
});
