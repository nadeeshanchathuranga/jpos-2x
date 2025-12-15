<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;

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

    // Execute Sync (Full Dynamic Mirror)
    public function sync(Request $request)
    {
        // 1. Get credentials from .env
        $host = env('DB_HOST_SECOND');
        $port = env('DB_PORT_SECOND');
        $database = env('DB_DATABASE_SECOND');
        $username = env('DB_USERNAME_SECOND');
        $password = env('DB_PASSWORD_SECOND');

        if (!$host || !$database || !$username) {
            return response()->json(['success' => false, 'message' => 'Secondary DB not configured properly.'], 400);
        }

        try {
            // 2. Connect to Secondary DB
            $secondDbConfig = [
                'driver' => 'mysql',
                'host' => $host,
                'port' => $port,
                'database' => $database,
                'username' => $username,
                'password' => $password,
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => false,
                'engine' => null,
            ];

            \Illuminate\Support\Facades\Config::set('database.connections.mysql_second', $secondDbConfig);
            \Illuminate\Support\Facades\DB::connection('mysql_second')->getPdo();

            // 3. Get All Tables from Main DB
            $tables = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
            $dbName = env('DB_DATABASE'); // Main DB name
            $tablesKey = "Tables_in_" . $dbName;

            // 4. Sync Process
            \Illuminate\Support\Facades\DB::connection('mysql_second')->statement('SET FOREIGN_KEY_CHECKS=0;');

            foreach ($tables as $tableObj) {
                // Determine table name dynamically
                // The key might be "Tables_in_jpos" or similar. 
                // We cast to array and get the first value to be safe.
                $tableArray = (array) $tableObj;
                $tableName = reset($tableArray);

                // Skip specific internal tables if needed (optional, syncing all is usually requested)
                // if ($tableName === 'migrations') continue; 

                // A. Get Create Table SQL from Main DB
                $createTableSql = \Illuminate\Support\Facades\DB::selectOne("SHOW CREATE TABLE `$tableName`");
                $createTableArray = (array) $createTableSql;
                // Key is usually "Create Table"
                $createSql = $createTableArray['Create Table'];

                // B. Drop Existing Table in Secondary DB
                \Illuminate\Support\Facades\DB::connection('mysql_second')->statement("DROP TABLE IF EXISTS `$tableName`");

                // C. Create Table in Secondary DB
                \Illuminate\Support\Facades\DB::connection('mysql_second')->statement($createSql);

                // D. Copy Data
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

            \Illuminate\Support\Facades\DB::connection('mysql_second')->statement('SET FOREIGN_KEY_CHECKS=1;');

            return response()->json([
                'success' => true,
                'message' => 'All tables synced successfully! Second DB is now a copy of Main DB.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
