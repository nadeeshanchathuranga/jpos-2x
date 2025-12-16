<?php

namespace App\Http\Controllers;

use App\Models\StockTransferReturn;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class StockTransferReturnReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->toDateString());

        $returns = StockTransferReturn::with(['user', 'products.product', 'products.measurementUnit'])
            ->whereBetween('return_date', [$startDate, $endDate])
            ->orderBy('return_date', 'desc')
            ->get()
            ->map(function ($return) {
                return [
                    'id' => $return->id,
                    'return_date' => $return->return_date,
                    'return_no' => $return->return_no,
                    'user_name' => $return->user->name ?? 'N/A',
                    'status' => $return->status,
                    'total_items' => $return->products->sum('stock_transfer_quantity'),
                    'reason' => $return->reason,
                    'products' => $return->products->map(function ($product) {
                        return [
                            'product_name' => $product->product->name ?? 'N/A',
                            'stock_transfer_quantity' => $product->stock_transfer_quantity,
                            'measurement_unit' => $product->measurementUnit->name ?? 'N/A',
                        ];
                    }),
                ];
            });

        return Inertia::render('Reports/StockTransferReturnReport', [
            'returns' => $returns,
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
