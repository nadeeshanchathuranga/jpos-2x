<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class SyncSettingController extends Controller
{
    public function index()
    {
        // Render the Sync Setting page (Inertia)
        return Inertia::render('Settings/SyncSetting');
    }
}
