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
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\PtrController;
use App\Http\Controllers\PrnController;
use App\Http\Controllers\InstallationController;
use App\Http\Controllers\CompanyInformationController;

// Installation Routes
Route::prefix('installation')->name('installation.')->group(function () {
    Route::get('/', [InstallationController::class, 'systemCheck'])->name('system-check');
    Route::post('/proceed', [InstallationController::class, 'proceedSetup'])->name('proceed-setup');

    Route::get('/composer', [InstallationController::class, 'composerInstall'])->name('composer');
    Route::post('/composer', [InstallationController::class, 'executeComposerInstall'])->name('composer-install');

    Route::get('/npm-install', [InstallationController::class, 'npmInstall'])->name('npm-install');
    Route::post('/npm-install', [InstallationController::class, 'executeNpmInstall'])->name('npm-install-execute');

    Route::get('/npm-build', [InstallationController::class, 'npmBuild'])->name('npm-build');
    Route::post('/npm-build', [InstallationController::class, 'executeNpmBuild'])->name('npm-build-execute');

    Route::get('/env-setup', [InstallationController::class, 'envSetup'])->name('env-setup');
    Route::post('/env-setup', [InstallationController::class, 'createEnv'])->name('create-env');

    Route::get('/env-config', [InstallationController::class, 'envConfig'])->name('env-config');
    Route::post('/env-config', [InstallationController::class, 'updateEnv'])->name('update-env');

    Route::get('/db-test', [InstallationController::class, 'dbTest'])->name('db-test');
    Route::post('/db-test', [InstallationController::class, 'createDatabase'])->name('create-database');

    Route::get('/migrate', [InstallationController::class, 'migrate'])->name('migrate');
    Route::post('/migrate', [InstallationController::class, 'executeMigrate'])->name('migrate-execute');

    Route::get('/seed-databases', [InstallationController::class, 'seedDatabases'])->name('seed-databases');
    Route::post('/seed-databases', [InstallationController::class, 'executeSeedDatabases'])->name('seed-databases-execute');

    Route::get('/generate-key', [InstallationController::class, 'generateKey'])->name('generate-key');
    Route::post('/generate-key', [InstallationController::class, 'executeGenerateKey'])->name('generate-key-execute');

    Route::get('/storage-link', [InstallationController::class, 'storageLink'])->name('storage-link');
    Route::post('/storage-link', [InstallationController::class, 'executeStorageLink'])->name('storage-link-execute');

    Route::get('/complete', [InstallationController::class, 'complete'])->name('complete');

    Route::post('/reset', [InstallationController::class, 'resetSetup'])->name('reset-setup');
});

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

    // Expense Routes
    Route::resource('expenses', ExpenseController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::get('/expenses/supplier-data', [ExpenseController::class, 'getSupplierData'])->name('expenses.supplier-data');

    // Company Settings Routes
    Route::get('/settings/company', [CompanyInformationController::class, 'index'])->name('settings.company');
    Route::post('/settings/company', [CompanyInformationController::class, 'store'])->name('settings.company.store');

    // PTR Routes
    Route::resource('ptr', PtrController::class);
    Route::patch('ptr/{ptr}/status', [PtrController::class, 'updateStatus'])->name('ptr.updateStatus');

    Route::get('/ptr/{id}/details', [PtrController::class, 'ptrDetails']);

    // PRN Routes
    Route::get('/prn', [PrnController::class, 'index'])->name('prn.index');
    Route::post('/prn', [PrnController::class, 'store'])->name('prn.store');
    Route::put('/prn/{prn}', [PrnController::class, 'update'])->name('prn.update');
    Route::delete('/prn/{prn}', [PrnController::class, 'destroy'])->name('prn.destroy');
});

Route::post('/brands', [BrandController::class, 'store'])->name('brands.store');
Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
Route::post('/types', [TypeController::class, 'store'])->name('types.store');
Route::post('/measurement-units', [MeasurementUnitController::class, 'store'])->name('measurement_units.store');



require __DIR__.'/auth.php';
