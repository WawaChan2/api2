<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\StatsController;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified', 'role.admin'])->group(function () {
    Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
});

Route::middleware(['auth', 'verified', 'role.user'])->group(function () {
    Route::get('catalog', [ProductController::class, 'index'])->name('catalog');
    Route::get('/catalog/{product}', [ProductController::class, 'show'])->name('detail');
    Route::get('orders', [OrderController::class, 'index'])->name('orders');
    Route::post('orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::get('stats', [StatsController::class, 'index'])->name('stats');
    Route::get('cart', [CartController::class, 'index'])->name('cart');
    Route::patch('/cart/{cartItem}/update', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{cartItem}/destroy', [CartController::class, 'destroy'])->name('cart.destroy');
});

require __DIR__.'/settings.php';
