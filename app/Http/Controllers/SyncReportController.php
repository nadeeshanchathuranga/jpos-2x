<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\ActivityLog;

class SyncReportController extends Controller
{
    /**
     * Display a listing of the sync activity logs.
     */
    public function index(Request $request)
    {
        // Fetch only sync-related logs and eager load user
        $logs = ActivityLog::with('user')
            ->where('module', 'sync setting')
            ->orderByDesc('created_at')
            ->paginate(50);

        // Map logs to include user_name
        $logs->getCollection()->transform(function ($log) {
            $log->user_name = $log->user ? $log->user->name : 'System';
            return $log;
        });

        return Inertia::render('Reports/SyncReport', [
            'logs' => $logs,
        ]);
    }
}
