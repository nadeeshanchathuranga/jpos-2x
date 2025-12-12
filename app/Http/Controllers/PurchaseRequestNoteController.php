<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\ProductReleaseNote;
use App\Models\ProductReleaseNoteProduct;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\ProductTransferRequest;
use App\Models\User;
use App\Models\ProductMovement;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\MeasurementUnit;

class PurchaseRequestNoteController extends Controller
{
    public function index()
    {
        $productReleaseNotes = ProductReleaseNote::with(['product_release_note_products.products', 'product_release_note_products.products.measurement_unit', 'user', 'product_transfer_request'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $suppliers = Supplier::where('status', '!=', 0)->get();
        $products = Product::all();
        $productTransferRequests = ProductTransferRequest::with(['product_transfer_request_products.product', 'product_transfer_request_products.product.measurement_unit'])->get();
        $users = User::all();
        $measurementUnits = MeasurementUnit::orderBy('name')->get();

    
 
        return Inertia::render('ProductReleaseNotes/Index', [
            'productReleaseNotes' => $productReleaseNotes,
            'suppliers' => $suppliers,
            'availableProducts' => $products,
            'productTransferRequests' => $productTransferRequests,
            'users' => $users,
            'measurementUnits' => $measurementUnits,
        ]);
    }

 public function store(Request $request)
{
    // Validate request
    $validated = $request->validate([
        'product_transfer_request_id' => 'required|exists:product_transfer_requests,id',
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
        $productReleaseNote = ProductReleaseNote::create([
            'product_transfer_request_id' => $validated['product_transfer_request_id'],
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

            ProductReleaseNoteProduct::create([
                'product_release_note_id' => $productReleaseNote->id,
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
                'ProductReleaseNote-' . $productReleaseNote->id
            );
            // Update product stock values: decrement store_quantity, increment shop_quantity
            $productModel = Product::find($product['product_id']);
            if ($productModel) {
                $quantity = is_numeric($product['quantity']) ? (float)$product['quantity'] : floatval($product['quantity']);
                $purchaseToTransfer = is_numeric($productModel->purchase_to_transfer_rate) && $productModel->purchase_to_transfer_rate > 0 ? (float)$productModel->purchase_to_transfer_rate : 1.0;
                $transferToSales = is_numeric($productModel->transfer_to_sales_rate) && $productModel->transfer_to_sales_rate > 0 ? (float)$productModel->transfer_to_sales_rate : 1.0;
                $converted = round($quantity * $purchaseToTransfer * $transferToSales, 4);
                // decrement store_quantity (leave storage), increment shop_quantity (arrive at shop)
                $productModel->decrement('store_quantity', $converted);
                $productModel->increment('shop_quantity', $converted);
            }
        }

        DB::commit();

        return redirect()
            ->route('product-release-notes.index')
            ->with('success', 'PRN created successfully!');

    } catch (\Throwable $e) {
        DB::rollBack();

        return redirect()
            ->back()
            ->withErrors(['error' => 'Failed to create PRN: ' . $e->getMessage()])
            ->withInput();
    }
}

    
    public function update(Request $request, ProductReleaseNote $productReleaseNote)
    {
        $validated = $request->validate([
            'product_transfer_request_id'   => 'required|exists:product_transfer_requests,id',
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
            $productReleaseNote->update([
                'product_transfer_request_id' => $validated['product_transfer_request_id'],
                'user_id'       => $validated['user_id'] ?? auth()->id(),
                'release_date'  => $validated['release_date'],
                'status'        => $validated['status'],
                'remark'        => $validated['remark'] ?? null,
            ]);

            // Get old products before deletion to reverse movements
            $oldProducts = ProductReleaseNoteProduct::where('product_release_note_id', $productReleaseNote->id)->get();
            
            // Reverse old product movements
            foreach ($oldProducts as $oldProduct) {
                ProductMovement::recordMovement(
                    $oldProduct->product_id,
                    ProductMovement::TYPE_PURCHASE_RETURN,
                    $oldProduct->quantity, // Positive to reverse the negative
                    'ProductReleaseNote-' . $productReleaseNote->id . '-REVERSED'
                );
                // reverse stock adjustments made when original PRN was created
                $productModel = Product::find($oldProduct->product_id);
                if ($productModel) {
                    $quantity = is_numeric($oldProduct->quantity) ? (float)$oldProduct->quantity : floatval($oldProduct->quantity);
                    $purchaseToTransfer = is_numeric($productModel->purchase_to_transfer_rate) && $productModel->purchase_to_transfer_rate > 0 ? (float)$productModel->purchase_to_transfer_rate : 1.0;
                    $transferToSales = is_numeric($productModel->transfer_to_sales_rate) && $productModel->transfer_to_sales_rate > 0 ? (float)$productModel->transfer_to_sales_rate : 1.0;
                    $converted = round($quantity * $purchaseToTransfer * $transferToSales, 4);
                    // undo: increment store_quantity (back to storage), decrement shop_quantity (remove from shop)
                    $productModel->increment('store_quantity', $converted);
                    $productModel->decrement('shop_quantity', $converted);
                }
            }

            ProductReleaseNoteProduct::where('product_release_note_id', $productReleaseNote->id)->delete();
            foreach ($validated['products'] as $product) {
                ProductReleaseNoteProduct::create([
                    'product_release_note_id'     => $productReleaseNote->id,
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
                    'PRN-' . $productReleaseNote->id
                );
                // Update product stock values for new entries
                $product = Product::find($product['product_id']);
                if ($productModel) {
                    $quantity = is_numeric($product['quantity']) ? (float)$product['quantity'] : floatval($product['quantity']);
                    $purchaseToTransfer = is_numeric($product->purchase_to_transfer_rate) && $product->purchase_to_transfer_rate > 0 ? (float)$product->purchase_to_transfer_rate : 1.0;
                    $transferToSales = is_numeric($product->transfer_to_sales_rate) && $product->transfer_to_sales_rate > 0 ? (float)$product->transfer_to_sales_rate : 1.0;
                    $converted = round($quantity * $purchaseToTransfer * $transferToSales, 4);
                    $product->decrement('store_quantity', $converted);
                    $product->increment('shop_quantity', $converted);
                }
            }

            DB::commit();

            return redirect()->route('product_release_note.index')->with('success', 'PRN updated successfully');

        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withErrors(['error' => 'Failed to update PRN: ' . $e->getMessage()])
                ->withInput();
        }
    }

    

    public function destroy(ProductReleaseNote $productReleaseNote)
    {
        DB::beginTransaction();

        try {
            // Before deleting, restore stock values for related products
            $existing = ProductReleaseNoteProduct::where('product_release_note_id', $productReleaseNote->id)->get();
            foreach ($existing as $ex) {
                $product = Product::find($ex->product_id);
                if ($product) {
                    $quantity = is_numeric($ex->quantity) ? (float)$ex->quantity : floatval($ex->quantity);
                    $purchaseToTransfer = is_numeric($product->purchase_to_transfer_rate) && $product->purchase_to_transfer_rate > 0 ? (float)$product->purchase_to_transfer_rate : 1.0;
                    $transferToSales = is_numeric($product->transfer_to_sales_rate) && $product->transfer_to_sales_rate > 0 ? (float)$product->transfer_to_sales_rate : 1.0;
                    $converted = round($quantity * $purchaseToTransfer * $transferToSales, 4);
                    // restore: increment store_quantity, decrement quantity
                    $product->increment('store_quantity', $converted);
                    $product->decrement('quantity', $converted);
                }
            }
            // Delete related products
            ProductReleaseNoteProduct::where('product_release_notes_id', $productReleaseNote->id)->delete();
            
            // Delete the PRN
            $productReleaseNote->delete();

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
