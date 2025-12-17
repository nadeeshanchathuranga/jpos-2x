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
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $userId = $request->input('user_id');

        $query = ActivityLog::with('user')
            ->where('module', 'sync setting');

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }
        if ($userId) {
            $query->where('user_id', $userId);
        }

        $logs = $query->orderByDesc('created_at')->paginate(50);

        // Map logs to include user_name
        $logs->getCollection()->transform(function ($log) {
            $log->user_name = $log->user ? $log->user->name : 'System';
            return $log;
        });

        // Get unique user IDs from logs
        $userIds = $logs->pluck('user_id')->unique()->filter()->all();
        $users = \App\Models\User::whereIn('id', $userIds)->get(['id', 'name']);

        return Inertia::render('Reports/SyncReport', [
            'logs' => $logs,
            'users' => $users,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'selectedUser' => $userId,
        ]);
    }
}
