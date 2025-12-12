<?php

namespace App\Http\Controllers;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Income;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Expense;
use App\Models\GoodsReceivedNote;
use App\Models\GoodsReceivedNoteProduct;
use App\Models\GoodsReceivedNoteReturn;
use App\Models\ProductMovement;
use App\Models\SalesProduct;
use App\Models\SalesReturnProduct;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
// use Barryvdh\DomPDF\Facade\Pdf;
// use Maatwebsite\Excel\Facades\Excel;
// use App\Exports\SalesReportExport;
// use App\Exports\ProductStockExport;
// use App\Exports\ExpensesReportExport;

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
        // Commented out - requires barryvdh/laravel-dompdf package
        /*
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
        */
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $sales = Sale::select('id', 'sale_date', 'type', 'total_amount', 'discount', 'net_amount', 'balance')
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->orderBy('sale_date', 'desc')
            ->get();

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.Components.sales-pdf', [
                'sales' => $sales,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ]);
            return $pdf->download('sales-report-' . date('Y-m-d') . '.pdf');
        }

        return back()->with('error', 'PDF export not available. Install barryvdh/laravel-dompdf package.');
    }

    public function exportExcel(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $sales = Sale::select('id', 'sale_date', 'type', 'total_amount', 'discount', 'net_amount', 'balance')
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->orderBy('sale_date', 'desc')
            ->get();

        $filename = 'sales-report-' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $columns = ['ID','Sale Date','Type','Total Amount','Discount','Net Amount','Balance'];

        $callback = function() use ($sales, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($sales as $s) {
                fputcsv($file, [
                    $s->id,
                    $s->sale_date,
                    $s->type,
                    $s->total_amount,
                    $s->discount,
                    $s->net_amount,
                    $s->balance,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportProductStockPdf()
    {
        // Commented out - requires barryvdh/laravel-dompdf package
        /*
        $productsStock = Product::select('id', 'name',   'qty', 'retail_price', 'wholesale_price')
            ->orderBy('name')
            ->get();
        
        $pdf = Pdf::loadView('reports.Components.product-stock-pdf', [
            'productsStock' => $productsStock,
            'reportDate' => date('Y-m-d'),
        ]);
        
        return $pdf->download('product-stock-report-' . date('Y-m-d') . '.pdf');
        */
        $productsStock = Product::select('id', 'name', 'qty', 'retail_price', 'wholesale_price')
            ->orderBy('name')
            ->get();

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.Components.product-stock-pdf', [
                'productsStock' => $productsStock,
                'reportDate' => date('Y-m-d'),
            ]);
            return $pdf->download('product-stock-report-' . date('Y-m-d') . '.pdf');
        }

        return back()->with('error', 'PDF export not available. Install barryvdh/laravel-dompdf package.');
    }

    public function exportProductStockExcel()
    {
        $productsStock = Product::select('id', 'name', 'qty', 'retail_price', 'wholesale_price')
            ->orderBy('name')
            ->get();

        $filename = 'product-stock-report-' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $columns = ['ID','Name','Stock','Retail Price','Wholesale Price'];

        $callback = function() use ($productsStock, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($productsStock as $p) {
                fputcsv($file, [
                    $p->id,
                    $p->name,
                    $p->qty,
                    $p->retail_price,
                    $p->wholesale_price,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportExpensesPdf(Request $request)
    {
        // Commented out - requires barryvdh/laravel-dompdf package
        /*
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
        */
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $expensesList = Expense::with(['user:id,name', 'supplier:id,name'])
            ->select('id', 'title', 'amount', 'remark', 'expense_date', 'payment_type', 'user_id', 'supplier_id', 'reference')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->orderBy('expense_date', 'desc')
            ->get();

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.Components.expenses-pdf', [
                'expensesList' => $expensesList,
                'totalExpenses' => $expensesList->sum('amount'),
                'startDate' => $startDate,
                'endDate' => $endDate,
            ]);
            return $pdf->download('expenses-report-' . date('Y-m-d') . '.pdf');
        }

        return back()->with('error', 'PDF export not available. Install barryvdh/laravel-dompdf package.');
    }

    public function exportExpensesExcel(Request $request)
    {
        // Commented out - requires maatwebsite/excel package
        /*
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        
        return Excel::download(
            new ExpensesReportExport($startDate, $endDate),
            'expenses-report-' . date('Y-m-d') . '.xlsx'
        );
        */
        return back()->with('error', 'Excel export not available. Install maatwebsite/excel package.');
    }

    // Individual Report Pages

    public function salesReport(Request $request)
    {
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
        
        $totalIncome = Income::whereBetween('income_date', [$startDate, $endDate])->sum('amount');
        
        // Sales summary with returns
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
                
                $totalReturns = DB::table('sales_return')
                    ->join('sales', 'sales_return.sale_id', '=', 'sales.id')
                    ->join('sales_return_products', 'sales_return.id', '=', 'sales_return_products.sales_return_id')
                    ->where('sales.type', $item->type)
                    ->where('sales_return.status', 1)
                    ->whereBetween('sales.sale_date', [$startDate, $endDate])
                    ->sum('sales_return_products.total');
                
                $netTotalAfterReturns = $item->net_total - $totalReturns;
                
                return [
                    'type' => $item->type,
                    'type_name' => $types[$item->type] ?? 'Unknown',
                    'total_sales' => $item->total_sales,
                    'gross_total' => number_format($item->gross_total, 2),
                    'total_discount' => number_format($item->total_discount, 2),
                    'net_total' => number_format($item->net_total, 2),
                    'total_returns' => number_format($totalReturns, 2),
                    'net_total_after_returns' => number_format($netTotalAfterReturns, 2),
                    'total_balance' => number_format($item->total_balance, 2),
                ];
            });
        
        $totalSalesCount = Sale::whereBetween('sale_date', [$startDate, $endDate])->count();
        
        // Product Sales Report
        $productSalesReport = Product::select('id', 'name', 'barcode')
            ->with([
                'salesProducts.sale' => function($query) use ($startDate, $endDate) {
                    $query->select('id', 'sale_date');
                },
                'salesProducts' => function($query) use ($startDate, $endDate) {
                    $query->select('id', 'product_id', 'quantity', 'price', 'total', 'sale_id')
                        ->whereHas('sale', function($q) use ($startDate, $endDate) {
                            $q->whereBetween('sale_date', [$startDate, $endDate]);
                        });
                },
                'returnProducts.salesReturn' => function($query) use ($startDate, $endDate) {
                    $query->select('id', 'return_date', 'status');
                },
                'returnProducts' => function($query) use ($startDate, $endDate) {
                    $query->select('id', 'product_id', 'quantity', 'price', 'total', 'sales_return_id')
                        ->whereHas('salesReturn', function($q) use ($startDate, $endDate) {
                            $q->whereBetween('return_date', [$startDate, $endDate])
                              ->where('status', 1);
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

                // Get the earliest sale date for this product in the range
                $salesDate = $product->salesProducts->sortBy(function($sp) {
                    return $sp->sale ? $sp->sale->sale_date : null;
                })->first();
                if ($salesDate && $salesDate->sale && $salesDate->sale->sale_date) {
                    $salesDateFormatted = \Carbon\Carbon::parse($salesDate->sale->sale_date)->format('Y-m-d');
                } else {
                    $salesDateFormatted = null;
                }

                $returnsDateObj = $product->returnProducts->sortBy(function($rp) {
                    return $rp->salesReturn ? $rp->salesReturn->return_date : null;
                })->first();
                if ($returnsDateObj && $returnsDateObj->salesReturn && $returnsDateObj->salesReturn->return_date) {
                    $returnsDateFormatted = \Carbon\Carbon::parse($returnsDateObj->salesReturn->return_date)->format('Y-m-d');
                } else {
                    $returnsDateFormatted = null;
                }

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'barcode' => $product->barcode,
                    'sales_date' => $salesDateFormatted,
                    'sales_quantity' => $totalSalesQty,
                    'sales_amount' => number_format($totalSalesAmount, 2),
                    'returns_date' => $returnsDateFormatted,
                    'returns_quantity' => $totalReturnsQty,
                    'returns_amount' => number_format($totalReturnsAmount, 2),
                    'net_sales_quantity' => $netSalesQty,
                    'net_sales_amount' => number_format($netSalesAmount, 2),
                ];
            })
            ->filter(function ($item) {
                return $item['sales_quantity'] > 0 || $item['returns_quantity'] > 0;
            })
            ->values();
        
        return Inertia::render('Reports/SalesReport', [
            'incomeSummary' => $incomeSummary,
            'salesSummary' => $salesSummary,
            'productSalesReport' => $productSalesReport,
            'totalIncome' => number_format($totalIncome, 2),
            'totalSalesCount' => $totalSalesCount,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    public function productSalesReport(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        
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
                              ->where('status', 1);
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
                return $item['sales_quantity'] > 0 || $item['returns_quantity'] > 0;
            })
            ->values();
        
        return Inertia::render('Reports/ProductSalesReport', [
            'productSalesReport' => $productSalesReport,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    public function stockReport()
    {
        $productsStock = Product::select('id', 'name', 'qty', 'retail_price', 'wholesale_price')
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
        
        return Inertia::render('Reports/StockReport', [
            'productsStock' => $productsStock,
        ]);
    }

    public function expensesReport(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        
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
        
        $totalExpenses = Expense::whereBetween('expense_date', [$startDate, $endDate])->sum('amount');
        
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
        
        return Inertia::render('Reports/ExpensesReport', [
            'expensesSummary' => $expensesSummary,
            'expensesList' => $expensesList,
            'totalExpenses' => number_format($totalExpenses, 2),
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    public function incomeReport(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        
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
        
        $totalIncome = Income::whereBetween('income_date', [$startDate, $endDate])->sum('amount');
        
        $incomeList = Income::select('id', 'source', 'amount', 'income_date', 'payment_type', 'sale_id')
            ->whereBetween('income_date', [$startDate, $endDate])
            ->orderBy('income_date', 'desc')
            ->get()
            ->map(function ($item) {
                $paymentTypes = ['Cash', 'Card', 'Credit'];
                return [
                    'id' => $item->id,
                    'source' => $item->source,
                    'amount' => number_format($item->amount, 2),
                    'income_date' => $item->income_date,
                    'payment_type' => $item->payment_type,
                    'payment_type_name' => $paymentTypes[$item->payment_type] ?? 'Unknown',
                    'sale_id' => $item->sale_id,
                ];
            });
        
        return Inertia::render('Reports/IncomeReport', [
            'incomeSummary' => $incomeSummary,
            'incomeList' => $incomeList,
            'totalIncome' => number_format($totalIncome, 2),
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    public function grnReport(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $grns = GoodsReceivedNote::with(['goods_received_note_products', 'supplier:id,name'])
            ->whereBetween('goods_received_note_date', [$startDate, $endDate])
            ->orderByDesc('goods_received_note_date')
            ->get();

        $grnRows = $grns->map(function ($grn) {
            $grossTotal = $grn->goods_received_note_products->reduce(function ($carry, $item) {
                return $carry + ((float) $item->quantity * (float) $item->purchase_price);
            }, 0);

            $lineDiscount = $grn->goods_received_note_products->sum('discount');
            $productsTotal = $grn->goods_received_note_products->sum('total');
            $headerDiscount = (float) ($grn->discount ?? 0);
            $taxTotal = (float) ($grn->tax_total ?? 0);
            $netTotal = $productsTotal - $headerDiscount + $taxTotal;

            return [
                'id' => $grn->id,
                'grn_no' => $grn->goods_received_note_no,
                'supplier_name' => $grn->supplier->name ?? 'N/A',
                'date' => $grn->goods_received_note_date,
                'items_count' => $grn->goods_received_note_products->sum('quantity'),
                'gross_total' => round($grossTotal, 2),
                'line_discount' => round($lineDiscount, 2),
                'header_discount' => round($headerDiscount, 2),
                'tax_total' => round($taxTotal, 2),
                'net_total' => round($netTotal, 2),
                'status' => $grn->status,
            ];
        });

        $grnTotals = [
            'count' => $grnRows->count(),
            'items_count' => $grnRows->sum('items_count'),
            'gross_total' => number_format($grnRows->sum('gross_total'), 2),
            'net_total' => number_format($grnRows->sum('net_total'), 2),
            'tax_total' => number_format($grnRows->sum('tax_total'), 2),
            'discount_total' => number_format($grnRows->sum('line_discount') + $grnRows->sum('header_discount'), 2),
        ];
        return Inertia::render('Reports/GrnReport', [
            'grnRows' => $grnRows,
            'grnTotals' => $grnTotals,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    public function grnReturnReport(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $returns = GoodsReceivedNoteReturn::with([
                'goodsReceivedNote:id,goods_received_note_no',
                'goodsReceivedNoteReturnProducts.product:id,name,purchase_price',
                'user:id,name',
            ])
            ->whereBetween('date', [$startDate, $endDate])
            ->orderByDesc('date')
            ->get();

        $priceLookup = GoodsReceivedNoteProduct::whereIn('goods_received_note_id', $returns->pluck('goods_received_note_id'))
            ->get()
            ->groupBy(function ($row) {
                return $row->goods_received_note_id . '-' . $row->product_id;
            });

        $returnRows = $returns->map(function ($return) use ($priceLookup) {
            $lineItems = [];
            $totalQty = 0;
            $estimatedValue = 0;

            foreach ($return->goodsReceivedNoteReturnProducts as $item) {
                $key = $return->goods_received_note_id . '-' . $item->product_id;
                $purchasePrice = optional(optional($priceLookup->get($key))[0])->purchase_price;

                if ($purchasePrice === null && $item->relationLoaded('product')) {
                    $purchasePrice = $item->product->purchase_price ?? 0;
                }

                $lineTotal = ((float) $item->quantity) * ((float) ($purchasePrice ?? 0));
                $totalQty += (float) $item->quantity;
                $estimatedValue += $lineTotal;

                $lineItems[] = [
                    'product_name' => $item->product->name ?? 'N/A',
                    'quantity' => (float) $item->quantity,
                    'estimated_value' => round($lineTotal, 2),
                ];
            }

            return [
                'id' => $return->id,
                'grn_no' => $return->goodsReceivedNote->goods_received_note_no ?? null,
                'date' => $return->date->format('Y-m-d'),
                'handled_by' => $return->user->name ?? 'N/A',
                'total_quantity' => round($totalQty, 2),
                'estimated_value' => round($estimatedValue, 2),
                'items' => $lineItems,
            ];
        });

        $returnTotals = [
            'count' => $returnRows->count(),
            'quantity' => $returnRows->sum('total_quantity'),
            'estimated_value' => number_format($returnRows->sum('estimated_value'), 2),
        ];

        return Inertia::render('Reports/GrnReturnReport', [
            'returnRows' => $returnRows,
            'returnTotals' => $returnTotals,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    public function productMovementReport(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $productId = $request->input('product_id', null);

        $movementTypes = [
            ProductMovement::TYPE_PURCHASE => 'Purchase (GRN)',
            ProductMovement::TYPE_PURCHASE_RETURN => 'Purchase Return (PRN)',
            ProductMovement::TYPE_TRANSFER => 'Transfer (PTR)',
            ProductMovement::TYPE_SALE => 'Sale',
            ProductMovement::TYPE_SALE_RETURN => 'Sale Return',
            ProductMovement::TYPE_GRN_RETURN => 'GRN Return',
            ProductMovement::TYPE_STOCK_TRANSFER_RETURN => 'Stock Transfer Return',
        ];

        $query = ProductMovement::with('product')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if ($productId) {
            $query->where('product_id', $productId);
        }

        $movements = $query->orderByDesc('created_at')->get();

        $movementRows = $movements->map(function ($movement) use ($movementTypes) {
            return [
                'id' => $movement->id,
                'product_name' => $movement->product->name ?? 'N/A',
                'product_code' => $movement->product->barcode ?? 'N/A',
                'movement_type' => $movementTypes[$movement->movement_type] ?? 'Unknown',
                'movement_type_id' => $movement->movement_type,
                'quantity' => round($movement->quantity, 2),
                'reference' => $movement->reference ?? '—',
                'date' => $movement->created_at->format('Y-m-d H:i:s'),
                'date_only' => $movement->created_at->format('Y-m-d'),
            ];
        });

        // Summary by movement type
        $summaryByType = [];
        foreach ($movementTypes as $typeId => $typeName) {
            $typeTotal = $movementRows->where('movement_type_id', $typeId)->sum('quantity');
            if ($typeTotal != 0) {
                $summaryByType[] = [
                    'type' => $typeName,
                    'quantity' => round($typeTotal, 2),
                    'count' => $movementRows->where('movement_type_id', $typeId)->count(),
                ];
            }
        }

        // Summary by product
        $summaryByProduct = $movements->groupBy('product_id')
            ->map(function ($items) {
                $product = $items->first()->product;
                return [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_code' => $product->barcode,
                    'inbound' => round(
                        $items->whereIn('movement_type', [
                            ProductMovement::TYPE_PURCHASE,
                            ProductMovement::TYPE_SALE_RETURN,
                            ProductMovement::TYPE_GRN_RETURN,
                        ])->sum('quantity'),
                        2
                    ),
                    'outbound' => round(
                        $items->whereIn('movement_type', [
                            ProductMovement::TYPE_SALE,
                            ProductMovement::TYPE_TRANSFER,
                            ProductMovement::TYPE_PURCHASE_RETURN,
                            ProductMovement::TYPE_STOCK_TRANSFER_RETURN,
                        ])->sum('quantity'),
                        2
                    ),
                    'net' => round($items->sum('quantity'), 2),
                ];
            })
            ->values();

        $totals = [
            'total_movements' => $movementRows->count(),
            'total_quantity_in' => round(
                $movementRows->whereIn('movement_type_id', [
                    ProductMovement::TYPE_PURCHASE,
                    ProductMovement::TYPE_SALE_RETURN,
                    ProductMovement::TYPE_GRN_RETURN,
                ])->sum('quantity'),
                2
            ),
            'total_quantity_out' => round(
                $movementRows->whereIn('movement_type_id', [
                    ProductMovement::TYPE_SALE,
                    ProductMovement::TYPE_TRANSFER,
                    ProductMovement::TYPE_PURCHASE_RETURN,
                    ProductMovement::TYPE_STOCK_TRANSFER_RETURN,
                ])->sum('quantity'),
                2
            ),
            'unique_products' => $summaryByProduct->count(),
        ];

        $products = Product::where('status', '!=', 0)->orderBy('name')->get();

        return Inertia::render('Reports/ProductMovementReport', [
            'movements' => $movementRows,
            'summaryByType' => $summaryByType,
            'summaryByProduct' => $summaryByProduct,
            'totals' => $totals,
            'products' => $products,
            'selectedProductId' => $productId,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }
    /**
     * Products Low Stock (Store & Shop)
     */
    public function lowStockReport(Request $request)
    {
        // Filters: optional date range (updated_at) and type: shop|store|both
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $filterType = $request->input('filter', 'both'); // shop, store, both

        $query = Product::select(
                'id', 'name', 'barcode', 'shop_quantity', 'shop_low_stock_margin', 'store_quantity', 'store_low_stock_margin', 'updated_at'
            );

        // Apply date filter on updated_at if provided
        if ($startDate && $endDate) {
            try {
                $s = Carbon::parse($startDate)->startOfDay();
                $e = Carbon::parse($endDate)->endOfDay();
                $query->whereBetween('updated_at', [$s, $e]);
            } catch (\Exception $ex) {
                // ignore invalid dates
            }
        }

        // Apply low-stock filter type
        if ($filterType === 'shop') {
            $query->whereColumn('shop_quantity', '<=', 'shop_low_stock_margin');
        } elseif ($filterType === 'store') {
            $query->whereColumn('store_quantity', '<=', 'store_low_stock_margin');
        } else {
            $query->where(function($q) {
                $q->whereColumn('shop_quantity', '<=', 'shop_low_stock_margin')
                  ->orWhereColumn('store_quantity', '<=', 'store_low_stock_margin');
            });
        }

        $products = $query->orderBy('name')->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'barcode' => $item->barcode,
                'shop_quantity' => (int) $item->shop_quantity,
                'shop_low_stock_margin' => (int) $item->shop_low_stock_margin,
                'store_quantity' => (int) $item->store_quantity,
                'store_low_stock_margin' => (int) $item->store_low_stock_margin,
                'shop_status' => $item->shop_quantity <= $item->shop_low_stock_margin ? 'Low' : 'OK',
                'store_status' => $item->store_quantity <= $item->store_low_stock_margin ? 'Low' : 'OK',
            ];
        });

        return Inertia::render('Reports/LowStockReport', [
            'products' => $products,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'filter' => $filterType,
        ]);
    }

    /**
     * Export low stock as CSV (compatible with Excel)
     */
    public function exportLowStockCsv(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $filterType = $request->input('filter', 'both');

        $query = Product::select(
                'id', 'name', 'barcode', 'shop_quantity', 'shop_low_stock_margin', 'store_quantity', 'store_low_stock_margin', 'updated_at'
            );

        if ($startDate && $endDate) {
            try {
                $s = Carbon::parse($startDate)->startOfDay();
                $e = Carbon::parse($endDate)->endOfDay();
                $query->whereBetween('updated_at', [$s, $e]);
            } catch (\Exception $ex) {
                // ignore invalid dates
            }
        }

        if ($filterType === 'shop') {
            $query->whereColumn('shop_quantity', '<=', 'shop_low_stock_margin');
        } elseif ($filterType === 'store') {
            $query->whereColumn('store_quantity', '<=', 'store_low_stock_margin');
        } else {
            $query->where(function($q) {
                $q->whereColumn('shop_quantity', '<=', 'shop_low_stock_margin')
                  ->orWhereColumn('store_quantity', '<=', 'store_low_stock_margin');
            });
        }

        $products = $query->orderBy('name')->get();

        $filename = 'low-stock-report-' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $columns = ['ID','Name','Barcode','Shop Qty','Shop Margin','Shop Status','Store Qty','Store Margin','Store Status'];

        $callback = function() use ($products, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($products as $p) {
                $shopStatus = $p->shop_quantity <= $p->shop_low_stock_margin ? 'Low' : 'OK';
                $storeStatus = $p->store_quantity <= $p->store_low_stock_margin ? 'Low' : 'OK';
                fputcsv($file, [
                    $p->id,
                    $p->name,
                    $p->barcode,
                    $p->shop_quantity,
                    $p->shop_low_stock_margin,
                    $shopStatus,
                    $p->store_quantity,
                    $p->store_low_stock_margin,
                    $storeStatus,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export low stock as PDF using barryvdh/laravel-dompdf if available
     */
    public function exportLowStockPdf(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $filterType = $request->input('filter', 'both');

        $query = Product::select(
                'id', 'name', 'barcode', 'shop_quantity', 'shop_low_stock_margin', 'store_quantity', 'store_low_stock_margin', 'updated_at'
            );

        if ($startDate && $endDate) {
            try {
                $s = Carbon::parse($startDate)->startOfDay();
                $e = Carbon::parse($endDate)->endOfDay();
                $query->whereBetween('updated_at', [$s, $e]);
            } catch (\Exception $ex) {
                // ignore invalid dates
            }
        }

        if ($filterType === 'shop') {
            $query->whereColumn('shop_quantity', '<=', 'shop_low_stock_margin');
        } elseif ($filterType === 'store') {
            $query->whereColumn('store_quantity', '<=', 'store_low_stock_margin');
        } else {
            $query->where(function($q) {
                $q->whereColumn('shop_quantity', '<=', 'shop_low_stock_margin')
                  ->orWhereColumn('store_quantity', '<=', 'store_low_stock_margin');
            });
        }

        $products = $query->orderBy('name')->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'barcode' => $item->barcode,
                'shop_quantity' => $item->shop_quantity,
                'shop_low_stock_margin' => $item->shop_low_stock_margin,
                'store_quantity' => $item->store_quantity,
                'store_low_stock_margin' => $item->store_low_stock_margin,
                'shop_status' => $item->shop_quantity <= $item->shop_low_stock_margin ? 'Low' : 'OK',
                'store_status' => $item->store_quantity <= $item->store_low_stock_margin ? 'Low' : 'OK',
            ];
        });

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.Components.low-stock-pdf', ['products' => $products]);
            return $pdf->download('low-stock-report-' . date('Y-m-d') . '.pdf');
        }

        return back()->with('error', 'PDF export not available. Install barryvdh/laravel-dompdf package.');
    }
}
