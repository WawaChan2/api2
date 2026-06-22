<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified', 'role.admin'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

Route::middleware(['auth', 'verified', 'role.user'])->group(function () {
    Route::inertia('catalog', 'ProductCatalog')->name('catalog');
    Route::get('orders', [OrderController::class, 'index'])->name('orders');
    Route::inertia('stats', 'UserStats')->name('stats');
    Route::inertia('cart', 'Cart')->name('cart');
});

require __DIR__.'/settings.php';
