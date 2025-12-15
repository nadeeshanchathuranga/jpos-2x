<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class SyncSettingController extends Controller
{
    public function testConnection(Request $request)
    {
        $host = $request->input('host');
        $database = $request->input('db');
        $username = $request->input('username');
        $password = $request->input('password');
        $port = $request->input('port');

        // Try to connect to the second DB
        try {
            $connection = new \PDO(
                "mysql:host=$host;port=$port;dbname=$database",
                $username,
                $password,
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_TIMEOUT => 3,
                ]
            );
            // If connection is successful
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function index()
    {
        // Get second DB info from .env
        $secondDb = [
            'host' => env('DB_HOST_SECOND'),
            'port' => env('DB_PORT_SECOND'),
            'database' => env('DB_DATABASE_SECOND'),
            'username' => env('DB_USERNAME_SECOND'),
            'password' => env('DB_PASSWORD_SECOND'),
        ];
        return Inertia::render('Settings/SyncSetting', [
            'secondDb' => $secondDb
        ]);
    }
}
