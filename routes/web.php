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
use App\Http\Controllers\PorController;
use App\Http\Controllers\GrnController;
use App\Http\Controllers\PtrController;
use App\Http\Controllers\PrnController;
 
 
 
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', fn() => Inertia::render('Dashboard'))
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/admin-dashboard', fn() => Inertia::render('AdminDashboard'))
    ->middleware(['auth', 'verified'])
    ->name('admin.dashboard');

Route::get('/user-dashboard', fn() => Inertia::render('UserDashboard'))
    ->middleware(['auth', 'verified'])
    ->name('user.dashboard');


Route::middleware('auth')->group(function () {

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // REST Resources
    Route::resources([
        'brands' => BrandController::class,
        'categories' => CategoryController::class,
        'types' => TypeController::class,
        'measurement-units' => MeasurementUnitController::class,
        'suppliers' => SupplierController::class,
        'customers' => CustomerController::class,
        'discounts' => DiscountController::class,
        'taxes' => TaxController::class,
        'users' => UserController::class,
        'products' => ProductController::class,
    ], [
        'only' => ['index', 'store', 'update', 'destroy']
    ]);

    Route::post('products/{product}/duplicate', [ProductController::class, 'duplicate'])
        ->name('products.duplicate');

    // Purchase Order Routes
    Route::prefix('por')->name('por.')->group(function () {
        Route::get('/', [PorController::class, 'index'])->name('index');
        Route::get('/create', [PorController::class, 'create'])->name('create');
        Route::post('/', [PorController::class, 'store'])->name('store');
        Route::get('/{por}', [PorController::class, 'show'])->name('show');
        Route::patch('/{por}', [PorController::class, 'update'])->name('update');
        Route::patch('/{por}/status', [PorController::class, 'updateStatus'])->name('update-status');
        Route::delete('/{por}', [PorController::class, 'destroy'])->name('destroy');
    });

    Route::get('/po/{id}/details', [PorController::class, 'poDetails']);


    // GRN Routes
    Route::prefix('grn')->name('grn.')->group(function () {
        Route::get('/', [GrnController::class, 'index'])->name('index');
        Route::post('/', [GrnController::class, 'store'])->name('store');
        Route::patch('/{grn}', [GrnController::class, 'update'])->name('update');
        Route::patch('/{grn}/status', [GrnController::class, 'updateStatus'])->name('update-status');
        Route::delete('/{grn}', [GrnController::class, 'destroy'])->name('destroy');
    });

    // PTR Routes
    Route::resource('ptr', PtrController::class);
    Route::patch('ptr/{ptr}/status', [PtrController::class, 'updateStatus'])->name('ptr.updateStatus');


      Route::prefix('prn')->name('prn.')->group(function () {
        Route::get('/', [PrnController::class, 'index'])->name('index');
        Route::post('/', [PrnController::class, 'store'])->name('store');        
        Route::patch('/{prn}', [PrnController::class, 'update'])->name('update');
        Route::patch('/{prn}/status', [PrnController::class, 'updateStatus'])->name('update-status');
        Route::delete('/{prn}', [PrnController::class, 'destroy'])->name('destroy');
    });
});

require __DIR__.'/auth.php';
