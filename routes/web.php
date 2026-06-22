<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    Route::inertia('catalog', 'ProductCatalog')->name('catalog');
    Route::inertia('order', 'OrderHistory')->name('order');
    Route::inertia('stats', 'UserStats')->name('stats');
    Route::inertia('cart', 'Cart')->name('cart');
});

require __DIR__.'/settings.php';
