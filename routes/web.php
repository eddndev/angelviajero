<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductCatalogController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::controller(ProductCatalogController::class)->group(function () {
    // Product Listing Page (PLP) - Uses explicit route model binding on the 'slug' field.
    Route::get('/categoria/{category:slug}', 'showCategory')->name('catalog.category');

    // Product Detail Page (PDP)
    Route::get('/producto/{product:slug}', 'showProduct')->name('product.show');
});


// --- Shopping Cart Routes ---
// Grouped for clarity and potential future middleware (e.g., cart validation).
Route::controller(CartController::class)->prefix('carrito')->name('cart.')->group(function () {
    // Route to display the cart's content.
    Route::get('/', 'show')->name('show');
    
    // Endpoint to add a SKU to the cart (typically via AJAX).
    Route::post('/agregar', 'add')->name('add');
    
    // Endpoint to update a specific cart item's quantity.
    Route::patch('/actualizar/{cartItem}', 'update')->name('update');
    
    // Endpoint to remove an item from the cart.
    Route::delete('/eliminar/{cartItem}', 'remove')->name('remove');
});


// --- Checkout Process Routes ---
// These routes handle the final conversion actions. They require authentication.
Route::middleware('auth')->group(function () {
    Route::post('/checkout/whatsapp', [CheckoutController::class, 'sendToWhatsApp'])
         ->name('checkout.whatsapp');
    
    // Future checkout methods (e.g., Stripe, PayPal) could be added here.
});


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
require __DIR__.'/api.php';