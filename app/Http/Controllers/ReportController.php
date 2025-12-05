<?php

namespace App\Http\Controllers;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Income;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Expense;
use App\Models\SalesProduct;
use App\Models\SalesReturnProduct;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesReportExport;
use App\Exports\ProductStockExport;
use App\Exports\ExpensesReportExport;

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
        
        // Sales summary with returns adjustment
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
            ->map(function ($item) use ($startDate, $endDate) {
                $types = [1 => 'Retail', 2 => 'Wholesale'];
                
                // Calculate total approved returns for this sale type
                $totalReturns = DB::table('sales_return')
                    ->join('sales', 'sales_return.sale_id', '=', 'sales.id')
                    ->join('sales_return_products', 'sales_return.id', '=', 'sales_return_products.sales_return_id')
                    ->where('sales.type', $item->type)
                    ->where('sales_return.status', 1) // Only approved returns
                    ->whereBetween('sales.sale_date', [$startDate, $endDate])
                    ->sum('sales_return_products.total');
                
                $grossTotal = $item->gross_total;
                $netTotal = $item->net_total;
                $netTotalAfterReturns = $netTotal - $totalReturns;
                
                return [
                    'type' => $item->type,
                    'type_name' => $types[$item->type] ?? 'Unknown',
                    'total_sales' => $item->total_sales,
                    'gross_total' => number_format($grossTotal, 2),
                    'total_discount' => number_format($item->total_discount, 2),
                    'net_total' => number_format($netTotal, 2),
                    'total_returns' => number_format($totalReturns, 2),
                    'net_total_after_returns' => number_format($netTotalAfterReturns, 2),
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
        
        // Expenses summary by payment type
        $expensesSummary = Expense::select(
                'payment_type',
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('COUNT(*) as transaction_count')
            )
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->groupBy('payment_type')
            ->get()
            ->map(function ($item) {
                $paymentTypes = [0 => 'Cash', 1 => 'Card', 2 => 'Credit'];
                return [
                    'payment_type' => $item->payment_type,
                    'payment_type_name' => $paymentTypes[$item->payment_type] ?? 'Unknown',
                    'total_amount' => number_format($item->total_amount, 2),
                    'transaction_count' => $item->transaction_count,
                ];
            });
        
        // Total expenses for the period
        $totalExpenses = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->sum('amount');
        
        // Expenses list with relations
        $expensesList = Expense::with(['user:id,name', 'supplier:id,name'])
            ->select('id', 'title', 'amount', 'remark', 'expense_date', 'payment_type', 'user_id', 'supplier_id', 'reference')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->orderBy('expense_date', 'desc')
            ->get()
            ->map(function ($item) {
                $paymentTypes = [0 => 'Cash', 1 => 'Card', 2 => 'Credit'];
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'remark' => $item->remark,
                    'amount' => number_format($item->amount, 2),
                    'expense_date' => $item->expense_date,
                    'payment_type' => $item->payment_type,
                    'payment_type_name' => $paymentTypes[$item->payment_type] ?? 'Unknown',
                    'reference' => $item->reference,
                    'user_name' => $item->user->name ?? 'N/A',
                    'supplier_name' => $item->supplier->name ?? 'N/A',
                ];
            });
        
        // Product-wise Sales and Returns Report
        $productSalesReport = Product::select('id', 'name', 'barcode')
            ->with([
                'salesProducts' => function($query) use ($startDate, $endDate) {
                    $query->select('id', 'product_id', 'quantity', 'price', 'total', 'sale_id')
                        ->whereHas('sale', function($q) use ($startDate, $endDate) {
                            $q->whereBetween('sale_date', [$startDate, $endDate]);
                        });
                },
                'returnProducts' => function($query) use ($startDate, $endDate) {
                    $query->select('id', 'product_id', 'quantity', 'price', 'total', 'sales_return_id')
                        ->whereHas('salesReturn', function($q) use ($startDate, $endDate) {
                            $q->whereBetween('return_date', [$startDate, $endDate])
                              ->where('status', 1); // Only approved returns
                        });
                }
            ])
            ->get()
            ->map(function ($product) {
                $totalSalesQty = $product->salesProducts->sum('quantity');
                $totalSalesAmount = $product->salesProducts->sum('total');
                $totalReturnsQty = $product->returnProducts->sum('quantity');
                $totalReturnsAmount = $product->returnProducts->sum('total');
                $netSalesQty = $totalSalesQty - $totalReturnsQty;
                $netSalesAmount = $totalSalesAmount - $totalReturnsAmount;
                
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'barcode' => $product->barcode,
                    'sales_quantity' => $totalSalesQty,
                    'sales_amount' => number_format($totalSalesAmount, 2),
                    'returns_quantity' => $totalReturnsQty,
                    'returns_amount' => number_format($totalReturnsAmount, 2),
                    'net_sales_quantity' => $netSalesQty,
                    'net_sales_amount' => number_format($netSalesAmount, 2),
                ];
            })
            ->filter(function ($item) {
                // Only show products that have sales or returns
                return $item['sales_quantity'] > 0 || $item['returns_quantity'] > 0;
            })
            ->values();
        
        return Inertia::render('Reports/Index', [
            'incomeSummary' => $incomeSummary,
            'salesSummary' => $salesSummary,
            'productsStock' => $productsStock,
            'expensesSummary' => $expensesSummary,
            'expensesList' => $expensesList,
            'productSalesReport' => $productSalesReport,
            'totalIncome' => number_format($totalIncome, 2),
            'totalExpenses' => number_format($totalExpenses, 2),
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

    public function exportExpensesPdf(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        
        $expensesList = Expense::with(['user:id,name', 'supplier:id,name'])
            ->select('id', 'title', 'amount', 'remark', 'expense_date', 'payment_type', 'user_id', 'supplier_id', 'reference')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->orderBy('expense_date', 'desc')
            ->get();
        
        $totalExpenses = $expensesList->sum('amount');
        
        $pdf = Pdf::loadView('reports.Components.expenses-pdf', [
            'expensesList' => $expensesList,
            'totalExpenses' => $totalExpenses,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
        
        return $pdf->download('expenses-report-' . date('Y-m-d') . '.pdf');
    }

    public function exportExpensesExcel(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        
        return Excel::download(
            new ExpensesReportExport($startDate, $endDate),
            'expenses-report-' . date('Y-m-d') . '.xlsx'
        );
    }
}
