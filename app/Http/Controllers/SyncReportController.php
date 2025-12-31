<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\ActivityLog;
use App\Models\User;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

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

    /**
     * Export sync report as PDF
     */
    public function exportPdf(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->toDateString());
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

        // Temporarily increase memory limit for large datasets
        ini_set('memory_limit', '512M');

        $logs = $query->orderBy('created_at', 'desc')->get()->map(function ($log) {
            return [
                'id' => $log->id,
                'user_name' => $log->user->name ?? 'System',
                'action' => $log->action,
                'module' => $log->module,
                'details' => $log->details,
                'created_at' => $log->created_at->timezone('Asia/Colombo')->toDateTimeString(),
            ];
        });

        if (class_exists(Pdf::class)) {
            $pdf = Pdf::loadView('reports.Components.sync-pdf', [
                'logs' => $logs,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'selectedUser' => $userId ? User::find($userId)?->name : 'All Users',
            ]);
            return $pdf->download('sync-report-' . date('Y-m-d') . '.pdf');
        }

        return back()->with('error', 'PDF export not available. Install barryvdh/laravel-dompdf package.');
    }

    /**
     * Export sync report as Excel/CSV
     */
    public function exportExcel(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->toDateString());
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

        $logs = $query->orderBy('created_at', 'desc')->get();

        return response()->stream(function () use ($logs) {
            $handle = fopen('php://output', 'w');
            
            // CSV header
            fputcsv($handle, ['ID', 'Date & Time', 'User', 'Module', 'Action', 'Details']);
            
            // CSV rows
            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->id,
                    $log->created_at->timezone('Asia/Colombo')->toDateTimeString(),
                    $log->user->name ?? 'System',
                    $log->module,
                    $log->action,
                    $log->details,
                ]);
            }
            
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="sync-report-' . date('Y-m-d') . '.csv"',
        ]);
    }
}
