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
}
