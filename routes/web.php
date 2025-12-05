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
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\InstallationController;
use App\Http\Controllers\CompanyInformationController;
use App\Http\Controllers\AppSettingController;
use App\Http\Controllers\SmtpSettingController;

/*
|--------------------------------------------------------------------------
| Installation Routes
|--------------------------------------------------------------------------
|
| These routes handle the initial system installation and setup process.
| They guide users through:
| - System requirements check
| - Composer and NPM dependencies installation
| - Environment configuration (.env file setup)
| - Database creation and connection testing
| - Running migrations and seeders
| - Generating application key
| - Creating storage symbolic link
|
| Access: Public (no authentication required during installation)
|
*/
use App\Http\Controllers\GrnReturnController;

Route::prefix('installation')->name('installation.')->middleware(['web'])->withoutMiddleware(['auth'])->group(function () {
    // One-Click Complete Installation Starter
    Route::get('/start', [InstallationController::class, 'oneClickStart'])->name('one-click-start');
    Route::post('/start/prepare', [InstallationController::class, 'prepareSystem'])->name('prepare-system');
    
    // Step 1: System Requirements Check
    Route::get('/', [InstallationController::class, 'systemCheck'])->name('system-check');
    Route::post('/proceed', [InstallationController::class, 'proceedSetup'])->name('proceed-setup');

    // Auto Installation (One-Click)
    Route::get('/auto-install', [InstallationController::class, 'autoInstall'])->name('auto-install');
    Route::post('/auto-install/execute', [InstallationController::class, 'executeAutoInstall'])->name('auto-install-execute');
    Route::get('/auto-install/status', [InstallationController::class, 'autoInstallStatus'])->name('auto-install-status');
    Route::post('/auto-install/start-server', [InstallationController::class, 'startServer'])->name('start-server');

    // Step 2: Composer Dependencies Installation
    Route::get('/composer', [InstallationController::class, 'composerInstall'])->name('composer');
    Route::post('/composer', [InstallationController::class, 'executeComposerInstall'])->name('composer-install');

    // Step 3: NPM Dependencies Installation
    Route::get('/npm-install', [InstallationController::class, 'npmInstall'])->name('npm-install');
    Route::post('/npm-install', [InstallationController::class, 'executeNpmInstall'])->name('npm-install-execute');

    // Step 4: NPM Build (Compile Frontend Assets)
    Route::get('/npm-build', [InstallationController::class, 'npmBuild'])->name('npm-build');
    Route::post('/npm-build', [InstallationController::class, 'executeNpmBuild'])->name('npm-build-execute');

    // Step 5: Environment File Setup
    Route::get('/env-setup', [InstallationController::class, 'envSetup'])->name('env-setup');
    Route::post('/env-setup', [InstallationController::class, 'createEnv'])->name('create-env');

    // Step 6: Environment Configuration (Database credentials)
    Route::get('/env-config', [InstallationController::class, 'envConfig'])->name('env-config');
    Route::post('/env-config', [InstallationController::class, 'updateEnv'])->name('update-env');

    // Step 7: Database Connection Test
    Route::get('/db-test', [InstallationController::class, 'dbTest'])->name('db-test');
    Route::post('/db-test', [InstallationController::class, 'createDatabase'])->name('create-database');

    // Step 8: Run Database Migrations
    Route::get('/migrate', [InstallationController::class, 'migrate'])->name('migrate');
    Route::post('/migrate', [InstallationController::class, 'executeMigrate'])->name('migrate-execute');

    // Step 9: Seed Database with Initial Data
    Route::get('/seed-databases', [InstallationController::class, 'seedDatabases'])->name('seed-databases');
    Route::post('/seed-databases', [InstallationController::class, 'executeSeedDatabases'])->name('seed-databases-execute');

    // Step 10: Generate Application Key
    Route::get('/generate-key', [InstallationController::class, 'generateKey'])->name('generate-key');
    Route::post('/generate-key', [InstallationController::class, 'executeGenerateKey'])->name('generate-key-execute');

    // Step 11: Create Storage Symbolic Link
    Route::get('/storage-link', [InstallationController::class, 'storageLink'])->name('storage-link');
    Route::post('/storage-link', [InstallationController::class, 'executeStorageLink'])->name('storage-link-execute');

    // Step 12: Installation Complete
    Route::get('/complete', [InstallationController::class, 'complete'])->name('complete');

    // Reset Setup (Start Over)
    Route::post('/reset', [InstallationController::class, 'resetSetup'])->name('reset-setup');
});

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
|
| Routes accessible without authentication
|
*/

// Welcome/Landing Page - Shows app info, login and register links
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

/*
|--------------------------------------------------------------------------
| Dashboard Routes
|--------------------------------------------------------------------------
|
| Role-based dashboard routes for authenticated users
| Middleware: auth, verified
|
*/

// Main Dashboard - Accessible to all authenticated users
Route::get('/dashboard', fn() => Inertia::render('Dashboard'))
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Admin Dashboard - For administrators with full system access
Route::get('/admin-dashboard', fn() => Inertia::render('AdminDashboard'))
    ->middleware(['auth', 'verified'])
    ->name('admin.dashboard');

// User Dashboard - For regular users with limited access
Route::get('/user-dashboard', fn() => Inertia::render('UserDashboard'))
    ->middleware(['auth', 'verified'])
    ->name('user.dashboard');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
|
| All routes below require authentication (auth middleware)
| Organized by functional area
|
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Profile Management Routes
    |--------------------------------------------------------------------------
    |
    | User profile management endpoints
    | - View/Edit profile information
    | - Update profile details
    | - Delete account
    |
    */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /*
    |--------------------------------------------------------------------------
    | REST Resource Routes (CRUD Operations)
    |--------------------------------------------------------------------------
    |
    | Standard CRUD operations for main entities
    | Actions: index (list), store (create), update, destroy (delete)
    |
    | Resources:
    | - sales: Sales transactions
    | - brands: Product brands
    | - categories: Product categories
    | - types: Product types
    | - measurement-units: Units of measurement (kg, liters, etc.)
    | - suppliers: Supplier management
    | - customers: Customer management
    | - discounts: Discount rules
    | - taxes: Tax rates
    | - users: System user management
    | - products: Product inventory
    |
    */
    Route::resources([
        'reports' => ReportController::class,
        'sales' => SaleController::class,
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

    /*
    |--------------------------------------------------------------------------
    | Product Additional Routes
    |--------------------------------------------------------------------------
    */
    
    // Duplicate Product - Create copy of existing product
    Route::post('products/{product}/duplicate', [ProductController::class, 'duplicate'])
        ->name('products.duplicate');

    /*
    |--------------------------------------------------------------------------
    | Purchase Order Routes (POR)
    |--------------------------------------------------------------------------
    |
    | Purchase Order management with status tracking
    | Includes full CRUD operations plus:
    | - View PO details
    | - Update PO status (pending, approved, received, etc.)
    |
    */
    Route::prefix('por')->name('por.')->group(function () {
        Route::get('/', [PorController::class, 'index'])->name('index');                      // List all POs
        Route::get('/create', [PorController::class, 'create'])->name('create');              // Create PO form
        Route::post('/', [PorController::class, 'store'])->name('store');                     // Save new PO
        Route::get('/{por}', [PorController::class, 'show'])->name('show');                   // View PO details
        Route::patch('/{por}', [PorController::class, 'update'])->name('update');             // Update PO
        Route::patch('/{por}/status', [PorController::class, 'updateStatus'])->name('update-status'); // Change PO status
        Route::delete('/{por}', [PorController::class, 'destroy'])->name('destroy');          // Delete PO
    });

    // Get Purchase Order Details (AJAX endpoint)
    Route::get('/po/{id}/details', [PorController::class, 'poDetails']);

    /*
    |--------------------------------------------------------------------------
    | Goods Received Note Routes (GRN)
    |--------------------------------------------------------------------------
    |
    | Track received goods from purchase orders
    | Includes status management for receiving workflow
    |
    */
    Route::prefix('grn')->name('grn.')->group(function () {
        Route::get('/', [GrnController::class, 'index'])->name('index');                      // List all GRNs
        Route::post('/', [GrnController::class, 'store'])->name('store');                     // Create new GRN
        Route::patch('/{grn}', [GrnController::class, 'update'])->name('update');             // Update GRN
        Route::patch('/{grn}/status', [GrnController::class, 'updateStatus'])->name('update-status'); // Change GRN status
        Route::delete('/{grn}', [GrnController::class, 'destroy'])->name('destroy');          // Delete GRN
    });

    /*
    |--------------------------------------------------------------------------
    | Expense Management Routes
    |--------------------------------------------------------------------------
    |
    | Track company expenses with supplier association
    | Includes supplier financial data retrieval
    |
    */
     // GRN Return Routes
    Route::prefix('grn-returns')->name('grn-returns.')->group(function () {
        Route::get('/', [GrnReturnController::class, 'index'])->name('index');
        Route::get('/create', [GrnReturnController::class, 'create'])->name('create');
        Route::post('/', [GrnReturnController::class, 'store'])->name('store');
        Route::delete('/{grnReturn}', [GrnReturnController::class, 'destroy']) ->name('destroy');
        Route::patch('/{grnReturn}', [GrnReturnController::class, 'update'])->name('update');

    });

    // Expense Routes
    Route::resource('expenses', ExpenseController::class)->only(['index', 'store', 'update', 'destroy']);
    
    // Get Supplier Financial Data (total, paid, balance) - AJAX endpoint
    Route::get('/expenses/supplier-data', [ExpenseController::class, 'getSupplierData'])->name('expenses.supplier-data');

    /*
    |--------------------------------------------------------------------------
    | Settings Routes
    |--------------------------------------------------------------------------
    |
    | Application and company configuration
    |
    */
    
    // Company Information Settings - Company profile, logo, contact details
    Route::get('/settings/company', [CompanyInformationController::class, 'index'])->name('settings.company');
    Route::post('/settings/company', [CompanyInformationController::class, 'store'])->name('settings.company.store');

    // App Settings - Application name, logo, icon, footer
    Route::get('/settings/app', [AppSettingController::class, 'index'])->name('settings.app');
    Route::post('/settings/app', [AppSettingController::class, 'store'])->name('settings.app.store');

    // SMTP Settings - Email configuration
    Route::get('/settings/smtp', [SmtpSettingController::class, 'index'])->name('settings.smtp');
    Route::post('/settings/smtp', [SmtpSettingController::class, 'store'])->name('settings.smtp.store');

    /*
    |--------------------------------------------------------------------------
    | Product Transfer Request Routes (PTR)
    |--------------------------------------------------------------------------
    |
    | Manage product transfers between locations/warehouses
    | Includes status workflow (pending, approved, completed)
    |
    */
    Route::resource('ptr', PtrController::class);                                              // Full CRUD for PTR
    Route::patch('ptr/{ptr}/status', [PtrController::class, 'updateStatus'])->name('ptr.updateStatus'); // Update PTR status

    // Get PTR Details (AJAX endpoint)
    Route::get('/ptr/{id}/details', [PtrController::class, 'ptrDetails']);

    /*
    |--------------------------------------------------------------------------
    | Product Release Note Routes (PRN)
    |--------------------------------------------------------------------------
    |
    | Track product releases/dispatches from inventory
    |
    */
    Route::get('/prn', [PrnController::class, 'index'])->name('prn.index');                   // List all PRNs
    Route::post('/prn', [PrnController::class, 'store'])->name('prn.store');                  // Create new PRN
    Route::put('/prn/{prn}', [PrnController::class, 'update'])->name('prn.update');           // Update PRN
    Route::delete('/prn/{prn}', [PrnController::class, 'destroy'])->name('prn.destroy');      // Delete PRN
    // PRN Routes
    Route::get('/prn', [PrnController::class, 'index'])->name('prn.index');
    Route::post('/prn', [PrnController::class, 'store'])->name('prn.store');
    Route::put('/prn/{prn}', [PrnController::class, 'update'])->name('prn.update');
    Route::delete('/prn/{prn}', [PrnController::class, 'destroy'])->name('prn.destroy');

    // Return Routes
    Route::prefix('return')->name('return.')->group(function () {
        Route::get('/', [ReturnController::class, 'index'])->name('index');
        Route::get('/{return}', [ReturnController::class, 'show'])->name('show');
        Route::post('/', [ReturnController::class, 'store'])->name('store');
        Route::post('/from-sales', [ReturnController::class, 'createFromSales'])->name('create-from-sales');
        Route::patch('/{return}/status', [ReturnController::class, 'updateStatus'])->name('update-status');
    });

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');
    Route::get('/reports/export/excel', [ReportController::class, 'exportExcel'])->name('reports.export.excel');
    Route::get('/reports/export/product-stock/pdf', [ReportController::class, 'exportProductStockPdf'])->name('reports.export.product-stock.pdf');
    Route::get('/reports/export/product-stock/excel', [ReportController::class, 'exportProductStockExcel'])->name('reports.export.product-stock.excel');
    Route::get('/reports/export/expenses/pdf', [ReportController::class, 'exportExpensesPdf'])->name('reports.export.expenses.pdf');
    Route::get('/reports/export/expenses/excel', [ReportController::class, 'exportExpensesExcel'])->name('reports.export.expenses.excel');
});

/*
|--------------------------------------------------------------------------
| Quick Add Routes (Modal Creation)
|--------------------------------------------------------------------------
|
| These routes allow quick creation of supporting data from modal windows
| Used when creating products or orders and need to add a new brand/category/etc.
| on the fly without leaving the current page.
|
| Note: These routes are duplicated outside the auth group for AJAX accessibility
|
*/

// Quick Add: Brand - Create new brand from modal
Route::post('/brands', [BrandController::class, 'store'])->name('brands.store');

// Quick Add: Category - Create new category from modal
Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');

// Quick Add: Type - Create new type from modal
Route::post('/types', [TypeController::class, 'store'])->name('types.store');

// Quick Add: Measurement Unit - Create new unit from modal
Route::post('/measurement-units', [MeasurementUnitController::class, 'store'])->name('measurement_units.store');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
|
| Loaded from auth.php - includes login, logout, register, password reset, etc.
|
*/
require __DIR__.'/auth.php';
