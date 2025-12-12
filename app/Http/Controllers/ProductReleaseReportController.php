<?php

namespace App\Http\Controllers;

use App\Models\ProductReleaseNote;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class ProductReleaseReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->toDateString());

        $releases = ProductReleaseNote::with(['user', 'product_transfer_request', 'product_release_note_products.product'])
            ->whereBetween('release_date', [$startDate, $endDate])
            ->orderBy('release_date', 'desc')
            ->get()
            ->map(function ($release) {
                return [
                    'id' => $release->id,
                    'release_date' => $release->release_date,
                    'product_transfer_request_no' => $release->product_transfer_request->product_transfer_request_no ?? 'N/A',
                    'user_name' => $release->user->name ?? 'N/A',
                    'status' => $release->status,
                    'total_items' => $release->product_release_note_products->sum('quantity'),
                    'products' => $release->product_release_note_products->map(function ($prnProduct) {
                        return [
                            'product_name' => $prnProduct->product->name ?? 'N/A',
                            'quantity' => $prnProduct->quantity,
                        ];
                    }),
                ];
            });

        return Inertia::render('Reports/ProductReleaseReport', [
            'releases' => $releases,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    public function exportPdf(Request $request)
    {
        // PDF export logic will be implemented here
        // For now, return a message
        return response()->json(['message' => 'PDF export functionality to be implemented']);
    }

    public function exportExcel(Request $request)
    {
        // Excel export logic will be implemented here
        // For now, return a message
        return response()->json(['message' => 'Excel export functionality to be implemented']);
    }
}
