<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\StatsController;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified', 'role.admin'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

Route::middleware(['auth', 'verified', 'role.user'])->group(function () {
    Route::get('catalog', [ProductController::class, 'index'])->name('catalog');
    Route::get('/catalog/{product}', [ProductController::class, 'show'])->name('detail');
    Route::get('orders', [OrderController::class, 'index'])->name('orders');
    Route::get('stats', [StatsController::class, 'index'])->name('stats');
    Route::post('orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::inertia('cart', 'Cart')->name('cart');
});

require __DIR__.'/settings.php';
