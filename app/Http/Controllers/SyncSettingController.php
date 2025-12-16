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
            'app setting' => ['app_settings', 'smtp_settings'],
            'sync setting' => ['sync_settings', 'syn_logs'],
            'bill setting' => ['bill_settings'],
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

        if (isset($mapping[$moduleName])) {
            $tablesToSync = $mapping[$moduleName];
        } else {
            return response()->json(['success' => false, 'message' => 'Unknown module'], 400);
        }

        try {
            $this->configureSecondDb();
            // Connection check
            \Illuminate\Support\Facades\DB::connection('mysql_second')->getPdo();

            // STEP 1: Detect changes BEFORE syncing
            $changedTables = $this->detectChangedTables($tablesToSync);

            // STEP 2: Perform the sync
            \Illuminate\Support\Facades\DB::connection('mysql_second')->statement('SET FOREIGN_KEY_CHECKS=0;');

            foreach ($tablesToSync as $tableName) {
                $this->syncSingleTable($tableName);
            }

            \Illuminate\Support\Facades\DB::connection('mysql_second')->statement('SET FOREIGN_KEY_CHECKS=1;');

            // STEP 3: Log only the tables that were changed
            $this->logDetectedChanges($moduleName, $changedTables);

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
            throw $e;
        }
    }

    /**
     * Detect which tables have changes BEFORE syncing
     */
    private function detectChangedTables($tables)
    {
        $changedTables = [];
        
        foreach ($tables as $tableName) {
            // Skip if table doesn't exist in primary
            if (!\Illuminate\Support\Facades\Schema::hasTable($tableName)) {
                continue;
            }
            
            try {
                // Get checksum from primary DB
                $primaryChecksum = \Illuminate\Support\Facades\DB::selectOne("CHECKSUM TABLE `$tableName`");
                $primarySum = $primaryChecksum->Checksum ?? 0;
                
                // Get checksum from secondary DB (if exists)
                $secondarySum = 0;
                try {
                    $secondaryChecksum = \Illuminate\Support\Facades\DB::connection('mysql_second')
                        ->selectOne("CHECKSUM TABLE `$tableName`");
                    $secondarySum = $secondaryChecksum->Checksum ?? 0;
                } catch (\Exception $e) {
                    // Table doesn't exist in secondary, treat as new
                    $secondarySum = 0;
                }
                
                // Determine action based on checksums
                if ($secondarySum == 0 && $primarySum > 0) {
                    // Table doesn't exist in secondary - INSERT
                    $changedTables[] = [
                        'table_name' => $tableName,
                        'action' => 'insert'
                    ];
                } elseif ($primarySum != $secondarySum) {
                    // Checksums differ - UPDATE
                    $changedTables[] = [
                        'table_name' => $tableName,
                        'action' => 'update'
                    ];
                }
                // If checksums match, no change - don't add to list
                
            } catch (\Exception $e) {
                // If checksum fails, skip this table
                continue;
            }
        }
        
        return $changedTables;
    }

    /**
     * Log the detected changes to syn_logs table
     */
    private function logDetectedChanges($moduleName, $changedTables)
    {
        if (empty($changedTables)) {
            return; // No changes to log
        }

        $userId = \Illuminate\Support\Facades\Auth::id();
        $now = now();
        
        $logData = [];
        foreach ($changedTables as $change) {
            $logData[] = [
                'table_name' => $change['table_name'],
                'module' => ucfirst($moduleName), // Capitalize module name (e.g., "Brands")
                'action' => $change['action'],
                'synced_at' => $now,
                'user_id' => $userId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        
        \Illuminate\Support\Facades\DB::table('syn_logs')->insert($logData);
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
