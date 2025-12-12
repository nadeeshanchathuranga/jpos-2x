<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ActivityLogReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->toDateString());
        $userId = $request->input('user_id');
        $module = $request->input('module');

        Log::info('ActivityLogReportController@index request', [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'user_id' => $userId,
            'module' => $module,
        ]);

        $query = ActivityLog::with('user')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if ($userId) {
            $query->where('user_id', $userId);
        }
        if ($module) {
            $query->where('module', $module);
        }

        $logs = $query->orderBy('created_at', 'desc')->get()->map(function ($log) {
            return [
                'id' => $log->id,
                'user_name' => $log->user->name ?? 'N/A',
                'action' => $log->action,
                'module' => $log->module,
                'details' => $log->details,
                'created_at' => $log->created_at->timezone('Asia/Colombo')->toDateTimeString(),
            ];
        });

        Log::info('ActivityLogReportController@index SQL', [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
        ]);

        $users = User::select('id', 'name')->get();
        $modules = ActivityLog::select('module')->distinct()->pluck('module');

        return Inertia::render('Reports/ActivityLogReport', [
            'logs' => $logs,
            'users' => $users,
            'modules' => $modules,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'selectedUser' => $userId,
            'selectedModule' => $module,
        ]);
    }
}
