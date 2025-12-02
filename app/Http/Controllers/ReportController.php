<?php

namespace App\Http\Controllers;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Income;
use App\Models\Sale;
use App\Models\Product;
use App\Models\ProductMovement;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesReportExport;
use App\Exports\ProductStockExport;
use App\Exports\ProductMovementsExport;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // Get date range from request or default to current month
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        
        // Income summary by payment type
        $incomeSummary = Income::select(
                'payment_type',
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('COUNT(*) as transaction_count')
            )
            ->whereBetween('income_date', [$startDate, $endDate])
            ->groupBy('payment_type')
            ->get()
            ->map(function ($item) {
                $paymentTypes = ['Cash', 'Card', 'Credit'];
                return [
                    'payment_type' => $item->payment_type,
                    'payment_type_name' => $paymentTypes[$item->payment_type] ?? 'Unknown',
                    'total_amount' => number_format($item->total_amount, 2),
                    'transaction_count' => $item->transaction_count,
                ];
            });
        
        // Total income for the period
        $totalIncome = Income::whereBetween('income_date', [$startDate, $endDate])
            ->sum('amount');
        
        // Sales summary
        $salesSummary = Sale::select(
                'type',
                DB::raw('COUNT(*) as total_sales'),
                DB::raw('SUM(total_amount) as gross_total'),
                DB::raw('SUM(discount) as total_discount'),
                DB::raw('SUM(net_amount) as net_total'),
                DB::raw('SUM(balance) as total_balance')
            )
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->groupBy('type')
            ->get()
            ->map(function ($item) {
                $types = [1 => 'Retail', 2 => 'Wholesale'];
                return [
                    'type' => $item->type,
                    'type_name' => $types[$item->type] ?? 'Unknown',
                    'total_sales' => $item->total_sales,
                    'gross_total' => number_format($item->gross_total, 2),
                    'total_discount' => number_format($item->total_discount, 2),
                    'net_total' => number_format($item->net_total, 2),
                    'total_balance' => number_format($item->total_balance, 2),
                ];
            });
        
        // Total sales count
        $totalSalesCount = Sale::whereBetween('sale_date', [$startDate, $endDate])->count();
        
        // Products stock summary
        $productsStock = Product::select('id', 'name',   'qty', 'retail_price', 'wholesale_price')
            ->orderBy('name')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                     
                    'stock' => $item->qty,
                    'retail_price' => number_format($item->retail_price, 2),
                    'wholesale_price' => number_format($item->wholesale_price, 2),
                    'stock_status' => $item->qty == 0 ? 'Out of Stock' : ($item->qty < 10 ? 'Low Stock' : 'In Stock'),
                ];
            });
        
        // Product Movements
        $productMovements = ProductMovement::with('product:id,name,barcode')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($item) {
                $movementTypes = [
                    0 => ['name' => 'Purchase (GRN)', 'color' => 'green'],
                    1 => ['name' => 'Purchase Return (PRN)', 'color' => 'red'],
                    2 => ['name' => 'Transfer (PTR)', 'color' => 'blue'],
                    3 => ['name' => 'Sale', 'color' => 'orange'],
                    4 => ['name' => 'Sale Return', 'color' => 'purple'],
                ];
                $type = $movementTypes[$item->movement_type] ?? ['name' => 'Unknown', 'color' => 'gray'];
                
                return [
                    'id' => $item->id,
                    'product_name' => $item->product->name ?? 'N/A',
                    'product_barcode' => $item->product->barcode ?? 'N/A',
                    'movement_type' => $item->movement_type,
                    'movement_type_name' => $type['name'],
                    'movement_type_color' => $type['color'],
                    'quantity' => number_format((float)$item->quantity, 2),
                    'reference' => $item->reference ?? 'N/A',
                    'date' => Carbon::parse($item->created_at)->format('M d, Y H:i'),
                ];
            });
        
        return Inertia::render('Reports/Index', [
            'incomeSummary' => $incomeSummary,
            'salesSummary' => $salesSummary,
            'productsStock' => $productsStock,
            'productMovements' => $productMovements,
            'totalIncome' => number_format($totalIncome, 2),
            'totalSalesCount' => $totalSalesCount,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    public function exportPdf(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        
        $salesSummary = Sale::select(
                'type',
                DB::raw('COUNT(*) as total_sales'),
                DB::raw('SUM(total_amount) as gross_total'),
                DB::raw('SUM(discount) as total_discount'),
                DB::raw('SUM(net_amount) as net_total'),
                DB::raw('SUM(balance) as total_balance')
            )
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->groupBy('type')
            ->get();
        
        $pdf = Pdf::loadView('reports.Components.sales-pdf', [
            'salesSummary' => $salesSummary,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
        
        return $pdf->download('sales-report-' . date('Y-m-d') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        
        return Excel::download(
            new SalesReportExport($startDate, $endDate),
            'sales-report-' . date('Y-m-d') . '.xlsx'
        );
    }

    public function exportProductStockPdf()
    {
        $productsStock = Product::select('id', 'name',   'qty', 'retail_price', 'wholesale_price')
            ->orderBy('name')
            ->get();
        
        $pdf = Pdf::loadView('reports.Components.product-stock-pdf', [
            'productsStock' => $productsStock,
            'reportDate' => date('Y-m-d'),
        ]);
        
        return $pdf->download('product-stock-report-' . date('Y-m-d') . '.pdf');
    }

    public function exportProductStockExcel()
    {
        return Excel::download(
            new ProductStockExport(),
            'product-stock-report-' . date('Y-m-d') . '.xlsx'
        );
    }

    public function exportProductMovementsPdf(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        
        $productMovements = ProductMovement::with('product:id,name,barcode')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->orderBy('created_at', 'desc')
            ->get();
        
        $pdf = Pdf::loadView('reports.Components.product-movements-pdf', [
            'productMovements' => $productMovements,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
        
        return $pdf->download('product-movements-report-' . date('Y-m-d') . '.pdf');
    }

    public function exportProductMovementsExcel(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        
        return Excel::download(
            new ProductMovementsExport($startDate, $endDate),
            'product-movements-report-' . date('Y-m-d') . '.xlsx'
        );
    }
}
