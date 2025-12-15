<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class SyncSettingController extends Controller
{
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
