<?php

namespace App\Http\Controllers;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SalesProduct;
use App\Models\Customer;
use App\Models\Income;
use App\Models\CompanyInformation;
use App\Models\ProductMovement;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Type;
use App\Models\BillSetting;
use App\Models\Discount;
use App\Models\Quotation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{

     public function index()
    {
        // Generate next invoice number
        $lastSale = Sale::latest('id')->first();
        $billSetting = BillSetting::latest('id')->first();

        $nextInvoiceNo = $lastSale ? 'INV-' . str_pad($lastSale->id + 1, 6, '0', STR_PAD_LEFT) : 'INV-000001';

        $customers = Customer::select('id', 'name')->get();
        $products = Product::select('id', 'name', 'barcode', 'retail_price', 'wholesale_price', 'shop_quantity', 'shop_low_stock_margin', 'image', 'brand_id', 'category_id', 'type_id', 'discount_id')
            ->where('shop_quantity', '>', 0)
            ->with(['brand:id,name', 'category:id,name', 'type:id,name', 'discount:id,name,value,type'])
            ->orderByRaw('CASE WHEN shop_quantity <= shop_low_stock_margin THEN 1 ELSE 0 END')
            ->orderBy('name')
            ->get();

        $brands = Brand::select('id', 'name')->get();
        $categories = Category::select('id', 'name')->get();
        $types = Type::select('id', 'name')->get();
        $discounts = Discount::select('id', 'name')->get();
        $currencySymbol  = CompanyInformation::first();

        // Get quotations for conversion to sales
        $quotations = Quotation::select('id', 'quotation_no', 'quotation_date', 'total_amount', 'discount', 'customer_id', 'type')
            ->with(['customer:id,name', 'products.product:id,name'])
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($quotation) {
                return [
                    'id' => $quotation->id,
                    'quotation_no' => $quotation->quotation_no,
                    'quotation_date' => $quotation->quotation_date,
                    'total_amount' => $quotation->total_amount,
                    'discount' => $quotation->discount,
                    'customer_id' => $quotation->customer_id,
                    'customer_name' => $quotation->customer->name ?? 'Walk-in',
                    'customer_type' => $quotation->type == 2 ? 'wholesale' : 'retail',
                    'items' => $quotation->products->map(function ($item) {
                        return [
                            'product_id' => $item->product_id,
                            'product_name' => $item->product->name ?? 'Unknown Product',
                            'quantity' => $item->quantity,
                            'price' => $item->price,
                        ];
                    }),
                ];
            });

        return Inertia::render('Sales/Index', [
            'invoice_no' => $nextInvoiceNo,
            'customers' => $customers,
            'products' => $products,
            'brands' => $brands,
            'categories' => $categories,
            'types' => $types,
            'billSetting' => $billSetting,
            'discounts' => $discounts,
            'currencySymbol' => $currencySymbol,
            'quotations' => $quotations,
        ]);
    }

    public function store(Request $request)
    {



        $request->validate([
            'invoice_no' => 'required|unique:sales,invoice_no',
            'customer_type' => 'required|in:retail,wholesale',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'payments' => 'required|array|min:1',
            'payments.*.payment_type' => 'required|in:0,1,2',
            'payments.*.amount' => 'required|numeric|min:0',
        ]);




        try {
            DB::beginTransaction();

            // Calculate totals
            $totalAmount = collect($request->items)->sum(function ($item) {
                return $item['price'] * $item['quantity'];
            });

            $discount = $request->discount ?? 0;
            $netAmount = $totalAmount - $discount;

            // Calculate total paid from all payments
            $totalPaid = collect($request->payments)->sum('amount');
            $balance = $netAmount - $totalPaid;

            // Convert customer_type to integer (1 = Retail, 2 = Wholesale)
            $type = $request->customer_type === 'wholesale' ? 2 : 1;


            // Create sale
            $sale = Sale::create([
                'invoice_no' => $request->invoice_no,
                'type' => $type,
                'customer_id' => $request->customer_id ?: null,
                'user_id' => auth()->id(),
                'total_amount' => $totalAmount,
                'discount' => $discount,
                'net_amount' => $netAmount,

                'balance' => $balance,
                'sale_date' => $request->sale_date,
            ]);

            // Create sale items and update stock
            foreach ($request->items as $item) {
                SalesProduct::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['price'] * $item['quantity'],
                ]);

                // Update product stock
                $product = Product::find($item['product_id']);
                $product->decrement('shop_quantity', $item['quantity']);

                // Record product movement (Sale - reduces stock)
                ProductMovement::recordMovement(
                    $item['product_id'],
                    ProductMovement::TYPE_SALE,
                    -$item['quantity'], // Negative for stock reduction
                    $request->invoice_no
                );
            }

            // Create income records for each payment separately
            foreach ($request->payments as $index => $payment) {
                $paymentTypeName = $this->getPaymentTypeName($payment['payment_type']);

                Income::create([
                    'sale_id' => $sale->id,
                    'source' => 'Sale - ' . $sale->invoice_no . ' (' . $paymentTypeName . ' #' . ($index + 1) . ')',
                    'amount' => $payment['amount'], // Individual payment amount
                    'income_date' => $request->sale_date,
                    'payment_type' => $payment['payment_type'],
                     
                ]);
            }

            DB::commit();

            return redirect()->route('sales.index')
                ->with('success', 'Sale completed successfully! Invoice: ' . $sale->invoice_no);

        } catch (\Exception $e) {
           dd($e);
            DB::rollBack();
            return back()->with('error', 'Sale failed: ' . $e->getMessage());
        }
    }


    private function getPaymentTypeName($type)
    {
        return ['Cash', 'Card', 'Credit'][$type] ?? 'Unknown';
    }


    public function salesHistory()
    {
        $sales = Sale::with([
                'customer',
                'user',
                'products.product',
                'returns.products',
                'returns.replacements'
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $billSetting = BillSetting::latest('id')->first();

       $currencySymbol  = CompanyInformation::first();


        // compute return impact
        $salesTransformed = $sales->through(function ($sale) {
            $returnedTotal = $sale->returns->sum(function ($ret) {
                if ($ret->return_type == \App\Models\SalesReturn::TYPE_CASH_RETURN) {
                    return (float) ($ret->refund_amount ?? 0);
                }
                return $ret->products->sum(fn($p) => (float) ($p->total ?? 0));
            });
            $netAfterReturn = max(0, ($sale->net_amount ?? 0) - $returnedTotal);

            return [
                'id' => $sale->id,
                'invoice_no' => $sale->invoice_no,
                'type' => $sale->type,
                'customer' => $sale->customer,
                'products' => $sale->products,
                'total_amount' => $sale->total_amount,
                'discount' => $sale->discount,
                'net_amount' => $sale->net_amount,
                'balance' => $sale->balance,
                'sale_date' => optional($sale->sale_date)->format('Y-m-d'),
                'returns_count' => $sale->returns->count(),
                'returns_total' => round($returnedTotal, 2),
                'net_after_return' => round($netAfterReturn, 2),
            ];
        });

        return Inertia::render('Sales/AllSales', [
            'sales' => $salesTransformed,
            'billSetting' => $billSetting,
            'currencySymbol' => $currencySymbol,
        ]);
    }




}
