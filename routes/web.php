<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

Route::get('/', function () {
    return view('welcome');
});

// Products CRUD
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::post('/products/store', [ProductController::class, 'store'])->name('products.store');
Route::get('/products/{id}/edit', [ProductController::class, 'edit'])->name('products.edit');
Route::post('/products/{id}/update', [ProductController::class, 'update'])->name('products.update');
Route::post('/products/{id}/delete', [ProductController::class, 'destroy'])->name('products.destroy');

// Import products (Excel/CSV)
Route::post('/products/import', [ProductController::class, 'import'])->name('products.import');

// Export products (Excel)
Route::get('/products/export', [ProductController::class, 'export'])->name('products.export');