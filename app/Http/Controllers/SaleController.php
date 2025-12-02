<?php

namespace App\Http\Controllers;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SalesProduct;
use App\Models\Customer;
use App\Models\Income;
use App\Models\ProductMovement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    
     public function index()
    {
        // Generate next invoice number
        $lastSale = Sale::latest('id')->first();
        $nextInvoiceNo = $lastSale ? 'INV-' . str_pad($lastSale->id + 1, 6, '0', STR_PAD_LEFT) : 'INV-000001';
        
        $customers = Customer::select('id', 'name')->get();
        $products = Product::select('id', 'name', 'barcode', 'retail_price', 'qty')
            ->where('qty', '>', 0)
            ->get();
 
        return Inertia::render('Sales/Index', [
            'invoice_no' => $nextInvoiceNo,
            'customers' => $customers,
            'products' => $products,
        ]);
    }

    public function store(Request $request)
    {
 
        $request->validate([
            'invoice_no' => 'required|unique:sales,invoice_no',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
            'payment_type' => 'required|in:0,1,2',
        ]);

        try {
            DB::beginTransaction();

            // Calculate totals
            $totalAmount = collect($request->items)->sum(function ($item) {
                return $item['price'] * $item['quantity'];
            });

            $discount = $request->discount ?? 0;
            $netAmount = $totalAmount - $discount;
            $balance = $netAmount - $request->paid_amount;

            // Create sale
            $sale = Sale::create([
                'invoice_no' => $request->invoice_no,
                'customer_id' => $request->customer_id ?: null,
                'user_id' => auth()->id(),
                'total_amount' => $totalAmount,
                'discount' => $discount,
                'net_amount' => $netAmount,
                'paid_amount' => $request->paid_amount,
                'balance' => $balance,
                'payment_type' => $request->payment_type,
                'sale_date' => $request->sale_date,
            ]);

            // Create sale items and update stock
            foreach ($request->items as $item) {
                SalesProduct::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total_price' => $item['price'] * $item['quantity'],
                ]);

                // Update product stock
                $product = Product::find($item['product_id']);
                $product->decrement('qty', $item['quantity']);

                // Record product movement (Sale - reduces stock)
                ProductMovement::recordMovement(
                    $item['product_id'],
                    ProductMovement::TYPE_SALE,
                    -$item['quantity'], // Negative for stock reduction
                    $request->invoice_no
                );
            }

            // Create income record
            Income::create([
                'sale_id' => $sale->id,
                'source' =>  $request->invoice_no,
                'amount' => $request->paid_amount, 
                'income_date' => $request->sale_date,
                'payment_type' => $request->payment_type,
            ]);

            DB::commit();

            return redirect()->route('sales.index')
                ->with('success', 'Sale completed successfully! Invoice: ' . $sale->invoice_no);

        } catch (\Exception $e) {

            dd($e);
            DB::rollBack();
            return back()->with('error', 'Sale failed: ' . $e->getMessage());
        }
    }
}
