<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class SyncSettingController extends Controller
{
    // Show sync settings page
    public function index()
    {
        $secondDb = [
            'host' => env('DB_HOST_SECOND'),
            'port' => env('DB_PORT_SECOND'),
            'database' => env('DB_DATABASE_SECOND'),
            'username' => env('DB_USERNAME_SECOND'),
            'password' => env('DB_PASSWORD_SECOND'),
        ];

        return Inertia::render('Settings/SyncSetting', [
            'secondDb' => $secondDb,
        ]);
    }

    // Update second DB credentials (NO REFRESH)
    public function updateSecondDb(Request $request)
    {
        // ✅ SPA-safe validation (NO redirect)
        $validator = Validator::make($request->all(), [
            'host' => 'required|string',
            'port' => 'required|string',
            'database' => 'required|string',
            'username' => 'required|string',
            'password' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $data = $validator->validated();

        try {
            // Update .env
            $envPath = base_path('.env');
            $env = file_get_contents($envPath);

            $env = preg_replace('/DB_HOST_SECOND=.*/', 'DB_HOST_SECOND=' . $data['host'], $env);
            $env = preg_replace('/DB_PORT_SECOND=.*/', 'DB_PORT_SECOND=' . $data['port'], $env);
            $env = preg_replace('/DB_DATABASE_SECOND=.*/', 'DB_DATABASE_SECOND=' . $data['database'], $env);
            $env = preg_replace('/DB_USERNAME_SECOND=.*/', 'DB_USERNAME_SECOND=' . $data['username'], $env);
            $env = preg_replace('/DB_PASSWORD_SECOND=.*/', 'DB_PASSWORD_SECOND=' . ($data['password'] ?? ''), $env);

            file_put_contents($envPath, $env);

            // Ensure database exists
            $pdo = new \PDO(
                "mysql:host={$data['host']};port={$data['port']}",
                $data['username'],
                $data['password'],
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );

            $pdo->exec(
                "CREATE DATABASE IF NOT EXISTS `{$data['database']}` 
                 CHARACTER SET utf8mb4 
                 COLLATE utf8mb4_general_ci;"
            );

            // Clear config cache
            \Artisan::call('config:clear');

            // Log activity
            $this->logActivity('save', 'sync setting', [
                'host' => $data['host'],
                'port' => $data['port'],
                'database' => $data['database'],
                'username' => $data['username'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Second DB saved successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // Test second DB connection (NO REFRESH)
    public function testConnection(Request $request)
    {
        try {
            new \PDO(
                "mysql:host={$request->host};port={$request->port};dbname={$request->db}",
                $request->username,
                $request->password,
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_TIMEOUT => 3,
                ]
            );

            // Log activity
            $this->logActivity('test', 'sync setting', [
                'host' => $request->host,
                'port' => $request->port,
                'database' => $request->db,
                'username' => $request->username,
            ]);

            return response()->json([
                'success' => true,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function getModuleMapping()
    {
        return [
            // Core Data
            'products' => ['products'],
            'brands' => ['brands'],
            'categories' => ['categories'],
            'types' => ['types'],
            'units' => ['measurement_units'],
            'purchase orders' => ['purchase_orders', 'purchase_order_products', 'purchase_order_requests', 'purchase_order_request_products'],
            'goods received' => ['goods_received_notes', 'goods_received_note_products'],
            'goods received notes return' => ['goods_received_note_returns', 'goods_received_note_return_products'],
            'expenses' => ['expenses'],
            'suppliers' => ['suppliers'],
            'product transfer request' => ['product_transfer_requests', 'product_transfer_request_products'],
            'product release notes' => ['product_release_notes', 'product_release_note_products'],
            'stock returns' => ['stock_transfer_returns', 'stock_transfer_return_products'],
            'customers' => ['customers'],
            'discounts' => ['discounts'],
            'taxes' => ['taxes'],
            'sales' => ['sales', 'sales_products'], // Assuming sales_products exists
            'product return' => ['sales_returns', 'sales_return_products'],
            
            // Reports (Data already synced by above modules, but listed for verification/UI)
            'sales report' => [], 
            'stock report' => [],
            'activity log' => ['activity_logs'],
            'expenses report' => [],
            'income report' => ['incomes'], // Incomes actually has a table
            'product release report' => [],
            'stock return report' => [],
            'low stock report' => [],
            'goods received notes report' => [],
            'goods received notes return report' => [],
            'product movement report' => ['products_movement'], // If table exists
            
            // System
            'users' => ['users', 'personal_access_tokens'],
            'company info' => ['company_informations'],
            'app setting' => ['app_settings', 'smtp_settings', 'sync_settings'],
        ];
    }

    // Get list of Modules that need syncing (All Modules)
    public function getSyncList()
    {
        try {
            // Setup Secondary DB Connection
            $this->configureSecondDb();

            // 1. Get All Modules
            $mapping = $this->getModuleMapping();
            $modulesToSync = array_keys($mapping);

            // 2. Check for Unmapped Tables ('Others')
            $mappedTables = [];
            foreach ($mapping as $tables) {
                $mappedTables = array_merge($mappedTables, $tables);
            }

            $currentTables = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
            $dbName = env('DB_DATABASE');
            $unmappedTables = [];

            foreach ($currentTables as $t) {
                $tn = ((array)$t)["Tables_in_" . $dbName] ?? reset($t);
                if (!in_array($tn, $mappedTables) && !in_array($tn, ['migrations', 'password_reset_tokens', 'sessions', 'failed_jobs'])) {
                    $unmappedTables[] = $tn;
                }
            }
            
            if (!empty($unmappedTables)) {
                $modulesToSync[] = 'Others';
            }

            return response()->json([
                'success' => true,
                'modules' => $modulesToSync
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to prepare sync list: ' . $e->getMessage()
            ], 500);
        }
    }

    // Sync a specific Module
    public function syncModule(Request $request)
    {
        $request->validate([
            'module' => 'required|string'
        ]);

        $moduleName = $request->module;
        $mapping = $this->getModuleMapping();
        $tablesToSync = [];

        if ($moduleName === 'Others') {
             // Logic for Others is tricky without passing the list again. 
             // Ideally we should sync unmapped tables. 
             // For simplicity, let's refetch unmapped tables or simple sync everything else?
             // Better strategy: The frontend calls sync, we likely want to be stateless.
             // But for now, let's just find unmapped tables again dynamically or 
             // more simply: Just pass the tables or assume 'Others' syncs remainders.
             // To be robust: We will sync ALL tables that are NOT in the mapping.
             $allMapped = [];
             foreach ($mapping as $mTables) $allMapped = array_merge($allMapped, $mTables);
             
             $allTables = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
             $dbName = env('DB_DATABASE');
             foreach ($allTables as $t) {
                 $tn = ((array)$t)["Tables_in_" . $dbName] ?? reset($t); // handle different fetch styles
                 if (!in_array($tn, $allMapped) && !in_array($tn, ['migrations', 'sessions'])) {
                     $tablesToSync[] = $tn;
                 }
             }
        } elseif (isset($mapping[$moduleName])) {
            $tablesToSync = $mapping[$moduleName];
        } else {
            return response()->json(['success' => false, 'message' => 'Unknown module'], 400);
        }

        try {
            $this->configureSecondDb();
            // Connection check
            \Illuminate\Support\Facades\DB::connection('mysql_second')->getPdo();

            \Illuminate\Support\Facades\DB::connection('mysql_second')->statement('SET FOREIGN_KEY_CHECKS=0;');

            foreach ($tablesToSync as $tableName) {
                $this->syncSingleTable($tableName);
            }

            \Illuminate\Support\Facades\DB::connection('mysql_second')->statement('SET FOREIGN_KEY_CHECKS=1;');

            // Log activity
            $this->logActivity('sync', 'sync setting', [
                'module' => $moduleName,
                'tables' => $tablesToSync,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Synced $moduleName"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Sync $moduleName failed: " . $e->getMessage()
            ], 500);
        }
    }

    // --- Helpers ---

    private function configureSecondDb()
    {
        $config = [
            'driver' => 'mysql',
            'host' => env('DB_HOST_SECOND'),
            'port' => env('DB_PORT_SECOND'),
            'database' => env('DB_DATABASE_SECOND'),
            'username' => env('DB_USERNAME_SECOND'),
            'password' => env('DB_PASSWORD_SECOND'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ];
        \Illuminate\Support\Facades\Config::set('database.connections.mysql_second', $config);
    }

    private function getChecksums($connection)
    {
        // CHECKSUM TABLE table1, table2...
        // Need to construct one query for all tables or loop. One query is better but string limit?
        // Loop is safer.
        $cheks = [];
        $tables = \Illuminate\Support\Facades\DB::connection($connection)->select('SHOW TABLES');
        $dbName = ($connection == 'mysql') ? env('DB_DATABASE') : env('DB_DATABASE_SECOND');
        
        foreach ($tables as $t) {
            // handle object -> array -> value
            $arr = (array)$t;
            $tableName = reset($arr);
            
            // Skip views if possible, CHECKSUM might fail on views or return 0
            // We'll try-catch individual checksums
            try {
                $res = \Illuminate\Support\Facades\DB::connection($connection)->selectOne("CHECKSUM TABLE `$tableName`");
                $cheks[$tableName] = $res->Checksum;
            } catch (\Exception $e) {
                // If it fails (e.g. View), ignore or set null
                $cheks[$tableName] = null;
            }
        }
        return $cheks;
    }

    private function syncSingleTable($tableName)
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable($tableName)) return;

        try {
            $createTableSql = \Illuminate\Support\Facades\DB::selectOne("SHOW CREATE TABLE `$tableName`");
            $createTableArray = (array) $createTableSql;
            $createSql = $createTableArray['Create Table'] ?? null;

            if ($createSql) {
                \Illuminate\Support\Facades\DB::connection('mysql_second')->statement("DROP TABLE IF EXISTS `$tableName`");
                \Illuminate\Support\Facades\DB::connection('mysql_second')->statement($createSql);

                \Illuminate\Support\Facades\DB::table($tableName)->orderByRaw('1')->chunk(1000, function ($rows) use ($tableName) {
                    $data = [];
                    foreach ($rows as $row) {
                        $data[] = (array) $row;
                    }
                    if (!empty($data)) {
                         \Illuminate\Support\Facades\DB::connection('mysql_second')->table($tableName)->insert($data);
                    }
                });
            }
        } catch (\Exception $e) {
            // Log or ignore? For now ignore individual table failures to allow module sync to continue?
            // No, user wants feedback. Let's throw to mark module as failed.
            throw $e;
        }
    }

    /**
     * Log activity to activity_logs table
     */
    private function logActivity($action, $module, $details = [])
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'module' => $module,
            'details' => json_encode($details),
        ]);
    }
}
