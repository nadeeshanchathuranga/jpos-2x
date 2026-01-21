<?php

namespace App\Http\Controllers;

use App\Models\StockTransferReturn;
use App\Models\StockTransferReturnProduct;
use App\Models\Product;
use App\Models\User;
use App\Models\ProductMovement;
use App\Models\MeasurementUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StockTransferReturnController extends Controller
{
    public function index()
    {
        $stockTransferReturns = StockTransferReturn::with(['user', 'products.product.measurement_unit', 'products.measurementUnit'])
            ->latest()
            ->paginate(10);

        $products = Product::with(['purchaseUnit', 'salesUnit', 'transferUnit'])
            ->where('status', '!=', 0)
            ->get()
            ->map(function ($product) {
                $units = collect([
                    $product->purchaseUnit,
                    $product->salesUnit,
                    $product->transferUnit
                ])->filter()->unique('id')->values();
                
                $product->measurement_units = $units;
                return $product;
            });

        $measurementUnits = MeasurementUnit::where('status', '!=', 0)->get();
        $users = User::all();
        $returnNo = $this->generateReturnNumber();

        return Inertia::render('StockTransferReturns/Index', [
            'stockTransferReturns' => $stockTransferReturns,
            'products' => $products,
            'measurementUnits' => $measurementUnits,
            'users' => $users,
            'returnNo' => $returnNo
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'return_no' => 'required|string|unique:stock_transfer_returns,return_no',
            'return_date' => 'required|date',
            'reason' => 'nullable|string',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.measurement_unit_id' => 'required|exists:measurement_units,id',
            'products.*.stock_transfer_quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $user_id = auth()->id();

            // Validate stock for all products first
            foreach ($validated['products'] as $productData) {
                $product = Product::findOrFail($productData['product_id']);
                if ($product->shop_quantity_in_sales_unit < $productData['stock_transfer_quantity']) {
                    DB::rollBack();
                    return back()->withErrors([
                        'products' => "Insufficient stock for {$product->name}. Available: {$product->shop_quantity_in_sales_unit}"
                    ]);
                }
            }

            // Create header record
            $stockTransferReturn = StockTransferReturn::create([
                'return_no' => $validated['return_no'],
                'user_id' => $user_id,
                'reason' => $validated['reason'] ?? null,
                'return_date' => $validated['return_date'],
                'status' => 'completed',
            ]);

            // Create detail records and update stock
            foreach ($validated['products'] as $productData) {
                // Create product line item
                StockTransferReturnProduct::create([
                    'stock_transfer_return_id' => $stockTransferReturn->id,
                    'product_id' => $productData['product_id'],
                    'measurement_unit_id' => $productData['measurement_unit_id'],
                    'stock_transfer_quantity' => $productData['stock_transfer_quantity'],
                ]);

                // Move stock: Shop → Store (with unit conversion)
                $product = Product::findOrFail($productData['product_id']);
                
                // Get conversion rates
                $purchaseToTransferRate = $product->purchase_to_transfer_rate ?? 1;
                $transferToSalesRate = $product->transfer_to_sales_rate ?? 1;
                
                // stock_transfer_quantity is in sales units (smallest unit)
                $quantityInSalesUnits = $productData['stock_transfer_quantity'];
                
                // Convert to purchase units for store increment
                $quantityInPurchaseUnits = $purchaseToTransferRate > 0 && $transferToSalesRate > 0 
                    ? $quantityInSalesUnits / ($purchaseToTransferRate * $transferToSalesRate)
                    : 0;
                
                $product->decrement('shop_quantity_in_sales_unit', $quantityInSalesUnits);
                $product->increment('store_quantity_in_purchase_unit', $quantityInPurchaseUnits);

                // Record movement
                ProductMovement::record(
                    $productData['product_id'],
                    ProductMovement::TYPE_STOCK_TRANSFER_RETURN,
                    $productData['stock_transfer_quantity'],
                    'StockTransferReturn-' . $stockTransferReturn->id
                );
            }

            DB::commit();

            return redirect()->route('stock-transfer-returns.index')
                ->with('success', 'Stock Transfer Return created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create return: ' . $e->getMessage()]);
        }
    }

    public function destroy(StockTransferReturn $stockTransferReturn)
    {
        DB::beginTransaction();

        try {
            // Reverse stock movement for all products (with conversion)
            foreach ($stockTransferReturn->products as $returnProduct) {
                $product = Product::findOrFail($returnProduct->product_id);
                
                // Get conversion rates
                $purchaseToTransferRate = $product->purchase_to_transfer_rate ?? 1;
                $transferToSalesRate = $product->transfer_to_sales_rate ?? 1;
                
                // stock_transfer_quantity is in sales units
                $quantityInSalesUnits = $returnProduct->stock_transfer_quantity;
                $quantityInPurchaseUnits = $purchaseToTransferRate > 0 && $transferToSalesRate > 0 
                    ? $quantityInSalesUnits / ($purchaseToTransferRate * $transferToSalesRate)
                    : 0;
                
                // Reverse: increment shop (sales units), decrement store (purchase units)
                $product->increment('shop_quantity_in_sales_unit', $quantityInSalesUnits);
                $product->decrement('store_quantity_in_purchase_unit', $quantityInPurchaseUnits);
            }

            // Delete will cascade to products table
            $stockTransferReturn->delete();

            DB::commit();

            return redirect()->route('stock-transfer-returns.index')
                ->with('success', 'Stock Transfer Return deleted successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to delete return: ' . $e->getMessage()]);
        }
    }

    private function generateReturnNumber()
    {
        $prefix = 'STR';
        $date = date('Ymd');
        $lastReturn = StockTransferReturn::whereDate('created_at', today())
            ->latest()
            ->first();

        $sequence = $lastReturn ? (int)substr($lastReturn->return_no, -4) + 1 : 1;

        return $prefix . '-' . $date . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function updateStatus(Request $request, StockTransferReturn $stockTransferReturn)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,approved,completed',
        ]);

        $stockTransferReturn->update(['status' => $validated['status']]);

        return redirect()->back()->with('success', 'Status updated successfully');
    }

    public function update(Request $request, StockTransferReturn $stockTransferReturn)
    {
        $validated = $request->validate([
            'return_date' => 'required|date',
            'reason' => 'nullable|string',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.measurement_unit_id' => 'required|exists:measurement_units,id',
            'products.*.stock_transfer_quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            // Reverse old stock movements
            foreach ($stockTransferReturn->products as $oldProduct) {
                $product = Product::findOrFail($oldProduct->product_id);
                $product->increment('shop_quantity', $oldProduct->stock_transfer_quantity);
                $product->decrement('store_quantity', $oldProduct->stock_transfer_quantity);
            }

            // Delete old products
            $stockTransferReturn->products()->delete();

            // Validate new products stock
            foreach ($validated['products'] as $productData) {
                $product = Product::findOrFail($productData['product_id']);
                if ($product->shop_quantity < $productData['stock_transfer_quantity']) {
                    DB::rollBack();
                    return back()->withErrors([
                        'products' => "Insufficient stock for {$product->name}. Available: {$product->shop_quantity}"
                    ]);
                }
            }

            // Update header
            $stockTransferReturn->update([
                'return_date' => $validated['return_date'],
                'reason' => $validated['reason'] ?? null,
            ]);

            // Create new products and update stock
            foreach ($validated['products'] as $productData) {
                StockTransferReturnProduct::create([
                    'stock_transfer_return_id' => $stockTransferReturn->id,
                    'product_id' => $productData['product_id'],
                    'measurement_unit_id' => $productData['measurement_unit_id'],
                    'stock_transfer_quantity' => $productData['stock_transfer_quantity'],
                ]);

                $product = Product::findOrFail($productData['product_id']);
                $product->decrement('shop_quantity', $productData['stock_transfer_quantity']);
                $product->increment('store_quantity', $productData['stock_transfer_quantity']);

                ProductMovement::record(
                    $productData['product_id'],
                    ProductMovement::TYPE_STOCK_TRANSFER_RETURN,
                    $productData['stock_transfer_quantity'],
                    'StockTransferReturn-' . $stockTransferReturn->id
                );
            }

            DB::commit();

            return redirect()->route('stock-transfer-returns.index')
                ->with('success', 'Stock Transfer Return updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to update return: ' . $e->getMessage()]);
        }
    }
}
