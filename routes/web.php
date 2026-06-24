<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\StatsController;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified', 'role.admin'])->group(function () {
    Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('inventory', [InventoryController::class, 'index'])->name('inventory');
    Route::post('inventory/restock', [InventoryController::class, 'restock'])->name('inventory.restock');
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
    Route::put('/cart/store', [CartController::class, 'store'])->name('cart.store');
    Route::post('/checkout/store', [CheckoutController::class, 'store'])->name('checkout.store');
});

require __DIR__.'/settings.php';
