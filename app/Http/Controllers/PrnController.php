<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\PrNote;
use App\Models\PrNoteProduct;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Ptr;
use App\Models\User;
use App\Models\ProductMovement;
use Inertia\Inertia;
use Illuminate\Http\Request;

class PrnController extends Controller
{
    public function index()
    {
        $prns = PrNote::with(['prn_products.product', 'user', 'ptr'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
 
        $suppliers = Supplier::where('status', '!=', 0)->get();
        $products = Product::all();
        $ptrs = Ptr::with(['ptr_products.product'])->get();
        $users = User::all();
          
        return Inertia::render('PrNote/Index', [
            'prns' => $prns,
            'suppliers' => $suppliers,
            'availableProducts' => $products,
            'ptrs' => $ptrs,
            'users' => $users,
        ]);
    }

 public function store(Request $request)
{
    // Validate request
    $validated = $request->validate([
        'ptr_id' => 'required|exists:ptrs,id',
        'user_id' => 'nullable|exists:users,id',
        'release_date' => 'required|date',
        'status' => 'required|in:0,1',
        'remark' => 'nullable|string',

        'products' => 'required|array|min:1',
        'products.*.product_id' => 'nullable|exists:products,id', // allow null for manual products
        'products.*.quantity' => 'required|numeric|min:0.01',
        'products.*.unit_price' => 'required|numeric|min:0',
        'products.*.total' => 'required|numeric|min:0',
    ]);

    DB::beginTransaction();

    try {
        // Create PRN Header
        $prn = PrNote::create([
            'ptr_id' => $validated['ptr_id'],
            'user_id' => $validated['user_id'] ?? auth()->id(),
            'release_date' => $validated['release_date'],
            'status' => $validated['status'],
            'remark' => $validated['remark'] ?? null,
        ]);

        // Create PRN Products
        foreach ($validated['products'] as $product) {
            // Skip products with null product_id (optional manual products)
            if (empty($product['product_id'])) {
                continue;
            }

            PrNoteProduct::create([
                'prn_id' => $prn->id,
                'product_id' => $product['product_id'],
                'quantity' => $product['quantity'],
                'unit_price' => $product['unit_price'],
                'total' => $product['total'],
            ]);

            // Record product movement (Purchase Return - reduces stock)
            ProductMovement::recordMovement(
                $product['product_id'],
                ProductMovement::TYPE_PURCHASE_RETURN,
                -$product['quantity'], // Negative for stock reduction
                'PRN-' . $prn->id
            );
            // Update product stock values: decrement storage_stock_qty, increment qty
            $prod = Product::find($product['product_id']);
            if ($prod) {
                $qty = is_numeric($product['quantity']) ? (float)$product['quantity'] : floatval($product['quantity']);
                $transferToSales = is_numeric($prod->transfer_to_sales_rate) && $prod->transfer_to_sales_rate > 0 ? (float)$prod->transfer_to_sales_rate : 1.0;
                $converted = round($qty * $transferToSales, 4);
                // decrement storage_stock_qty, increment qty
                $prod->decrement('storage_stock_qty', $converted);
                $prod->increment('qty', $converted);
            }
        }

        DB::commit();

        return redirect()
            ->route('prn.index')
            ->with('success', 'PRN created successfully!');

    } catch (\Throwable $e) {
        DB::rollBack();

        return redirect()
            ->back()
            ->withErrors(['error' => 'Failed to create PRN: ' . $e->getMessage()])
            ->withInput();
    }
}

    
    public function update(Request $request, PrNote $prn)
    {
        $validated = $request->validate([
            'ptr_id'                        => 'required|exists:ptrs,id',
            'user_id'                       => 'nullable|exists:users,id',
            'release_date'                  => 'required|date',
            'status'                        => 'required|in:0,1',
            'remark'                        => 'nullable|string',

            'products'                      => 'required|array|min:1',
            'products.*.product_id'         => 'required|exists:products,id',
            'products.*.quantity'           => 'required|numeric|min:0.01',
            'products.*.unit_price'         => 'required|numeric|min:0',
            'products.*.total'              => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $prn->update([
                'ptr_id'        => $validated['ptr_id'],
                'user_id'       => $validated['user_id'] ?? auth()->id(),
                'release_date'  => $validated['release_date'],
                'status'        => $validated['status'],
                'remark'        => $validated['remark'] ?? null,
            ]);

            // Get old products before deletion to reverse movements
            $oldProducts = PrNoteProduct::where('prn_id', $prn->id)->get();
            
            // Reverse old product movements
            foreach ($oldProducts as $oldProduct) {
                ProductMovement::recordMovement(
                    $oldProduct->product_id,
                    ProductMovement::TYPE_PURCHASE_RETURN,
                    $oldProduct->quantity, // Positive to reverse the negative
                    'PRN-' . $prn->id . '-REVERSED'
                );
                // reverse stock adjustments made when original PRN was created
                $prod = Product::find($oldProduct->product_id);
                if ($prod) {
                    $qty = is_numeric($oldProduct->quantity) ? (float)$oldProduct->quantity : floatval($oldProduct->quantity);
                    $purchaseToTransfer = is_numeric($prod->purchase_to_transfer_rate) && $prod->purchase_to_transfer_rate > 0 ? (float)$prod->purchase_to_transfer_rate : 1.0;
                    $transferToSales = is_numeric($prod->transfer_to_sales_rate) && $prod->transfer_to_sales_rate > 0 ? (float)$prod->transfer_to_sales_rate : 1.0;
                    $converted = round($qty * $purchaseToTransfer * $transferToSales, 4);
                    // undo: increment storage_stock_qty, decrement qty
                    $prod->increment('storage_stock_qty', $converted);
                    $prod->decrement('qty', $converted);
                }
            }

            PrNoteProduct::where('prn_id', $prn->id)->delete();

            foreach ($validated['products'] as $product) {
                PrNoteProduct::create([
                    'prn_id'     => $prn->id,
                    'product_id' => $product['product_id'],
                    'quantity'   => $product['quantity'],
                    'unit_price' => $product['unit_price'],
                    'total'      => $product['total'],
                ]);

                // Record new product movement
                ProductMovement::recordMovement(
                    $product['product_id'],
                    ProductMovement::TYPE_PURCHASE_RETURN,
                    -$product['quantity'], // Negative for stock reduction
                    'PRN-' . $prn->id
                );
                // Update product stock values for new entries
                $prod = Product::find($product['product_id']);
                if ($prod) {
                    $qty = is_numeric($product['quantity']) ? (float)$product['quantity'] : floatval($product['quantity']);
                    $purchaseToTransfer = is_numeric($prod->purchase_to_transfer_rate) && $prod->purchase_to_transfer_rate > 0 ? (float)$prod->purchase_to_transfer_rate : 1.0;
                    $transferToSales = is_numeric($prod->transfer_to_sales_rate) && $prod->transfer_to_sales_rate > 0 ? (float)$prod->transfer_to_sales_rate : 1.0;
                    $converted = round($qty * $purchaseToTransfer * $transferToSales, 4);
                    $prod->decrement('storage_stock_qty', $converted);
                    $prod->increment('qty', $converted);
                }
            }

            DB::commit();

            return redirect()->route('prn.index')->with('success', 'PRN updated successfully');

        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withErrors(['error' => 'Failed to update PRN: ' . $e->getMessage()])
                ->withInput();
        }
    }

    

    public function destroy(PrNote $prn)
    {
        DB::beginTransaction();

        try {
            // Before deleting, restore stock values for related products
            $existing = PrNoteProduct::where('prn_id', $prn->id)->get();
            foreach ($existing as $ex) {
                $prod = Product::find($ex->product_id);
                if ($prod) {
                    $qty = is_numeric($ex->quantity) ? (float)$ex->quantity : floatval($ex->quantity);
                    $purchaseToTransfer = is_numeric($prod->purchase_to_transfer_rate) && $prod->purchase_to_transfer_rate > 0 ? (float)$prod->purchase_to_transfer_rate : 1.0;
                    $transferToSales = is_numeric($prod->transfer_to_sales_rate) && $prod->transfer_to_sales_rate > 0 ? (float)$prod->transfer_to_sales_rate : 1.0;
                    $converted = round($qty * $purchaseToTransfer * $transferToSales, 4);
                    // restore: increment storage_stock_qty, decrement qty
                    $prod->increment('storage_stock_qty', $converted);
                    $prod->decrement('qty', $converted);
                }
            }
            // Delete related products
            PrNoteProduct::where('prn_id', $prn->id)->delete();
            
            // Delete the PRN
            $prn->delete();

            DB::commit();

            return redirect()->back()->with('success', 'PRN deleted successfully');

        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withErrors(['error' => 'Failed to delete PRN: ' . $e->getMessage()]);
        }
    }
}
