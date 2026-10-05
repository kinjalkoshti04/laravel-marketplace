<?php

use App\Http\Controllers\AjaxController;
use App\Http\Controllers\BrowseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\MyListingController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
 | Public pages
 */
Route::get('/', HomeController::class)->name('home');
Route::get('/listings', [BrowseController::class, 'index'])->name('listings.index');
Route::get('/category/{category}', [BrowseController::class, 'category'])->name('browse.category');
Route::get('/city/{city}', [BrowseController::class, 'city'])->name('browse.city');
Route::get('/city/{city}/{category}', [BrowseController::class, 'cityCategory'])->name('browse.city-category');
Route::get('/listing/{listing}', [ListingController::class, 'show'])->name('listings.show');

/*
 | Dependent dropdowns on the listing form
 */
Route::prefix('ajax')->name('ajax.')->controller(AjaxController::class)->group(function () {
    Route::get('/states', 'states')->name('states');
    Route::get('/cities', 'cities')->name('cities');
    Route::get('/areas', 'areas')->name('areas');
    Route::get('/subcategories', 'subcategories')->name('subcategories');
});

/*
 | Logged-in users
 */
Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Guests are sent to login first and come back to the ad with the phone shown.
    Route::get('/listing/{listing}/contact', [ListingController::class, 'contact'])->name('listings.contact');

    Route::prefix('dashboard')->group(function () {
        Route::resource('listings', MyListingController::class)->except('show')->names('my-listings');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
