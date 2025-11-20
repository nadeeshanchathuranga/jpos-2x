<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TypeController;
use App\Http\Controllers\MeasurementUnitController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('brands', BrandController::class)->only([
        'index', 'store', 'update', 'destroy'
    ]);

    Route::resource('categories', CategoryController::class)->only([
        'index', 'store', 'update', 'destroy'
    ]);

    Route::resource('types', TypeController::class)->only([
        'index', 'store', 'update', 'destroy'
    ]);

    Route::resource('measurement-units', MeasurementUnitController::class)->only([
        'index', 'store', 'update', 'destroy'
    ]);

    Route::resource('suppliers', SupplierController::class)->only([
        'index', 'store', 'update', 'destroy'
    ]);

    Route::resource('customers', CustomerController::class)->only([
        'index', 'store', 'update', 'destroy'
    ]);

    Route::resource('discounts', DiscountController::class)->only([
        'index', 'store', 'update', 'destroy'
    ]);

    Route::resource('taxes', TaxController::class)->only([
        'index', 'store', 'update', 'destroy'
    ]);

    Route::resource('users', UserController::class)->only([
        'index', 'store', 'update', 'destroy'
    ]);

    Route::resource('products', ProductController::class)->only([
        'index', 'store', 'update', 'destroy'
    ]);
    Route::post('products/{product}/duplicate', [ProductController::class, 'duplicate'])->name('products.duplicate');
});

require __DIR__.'/auth.php';
