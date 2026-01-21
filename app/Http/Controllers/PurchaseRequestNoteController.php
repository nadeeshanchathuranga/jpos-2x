<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\ProductReleaseNote;
use App\Models\ProductReleaseNoteProduct;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\ProductTransferRequest;
use App\Models\User;
use App\Models\CompanyInformation;
use App\Models\ProductMovement;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\MeasurementUnit;

class PurchaseRequestNoteController extends Controller
{
    public function index()
    {
          $productReleaseNotes  = ProductReleaseNote::with(['product_release_note_products.product', 'product_release_note_products.product.measurement_unit', 'user', 'product_transfer_request'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        $suppliers = Supplier::where('status', '!=', 0)->get();
        $products = Product::with(['purchaseUnit', 'transferUnit', 'salesUnit'])->get();
        // Exclude completed PTRs from dropdown
        $productTransferRequests = ProductTransferRequest::with(['product_transfer_request_products.product', 'product_transfer_request_products.product.measurement_unit'])
            ->where('status', '!=', 'completed')
            ->get();
        $users = User::all();
        $measurementUnits = MeasurementUnit::orderBy('name')->get();
               $currencySymbol  = CompanyInformation::first();

    
 
        return Inertia::render('ProductReleaseNotes/Index', [
            'productReleaseNotes' => $productReleaseNotes,
            'suppliers' => $suppliers,
            'availableProducts' => $products,
            'productTransferRequests' => $productTransferRequests,
            'users' => $users,
            'measurementUnits' => $measurementUnits,
            'currencySymbol' => $currencySymbol,
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
        'products.*.product_id' => 'nullable|exists:products,id',
        'products.*.quantity' => 'required|numeric|min:0.01',
        'products.*.unit_price' => 'required|numeric|min:0',
        'products.*.total' => 'required|numeric|min:0',
        'products.*.unit_id' => 'nullable|exists:measurement_units,id',
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

        // Get PTR to check the unit used for each product
        $ptr = ProductTransferRequest::with('product_transfer_request_products')->find($validated['product_transfer_request_id']);

        // Create PRN Products
        foreach ($validated['products'] as $product) {
            // Skip products with null product_id
            if (empty($product['product_id'])) {
                continue;
            }

            ProductReleaseNoteProduct::create([
                'product_release_note_id' => $productReleaseNote->id,
                'product_id' => $product['product_id'],
                'unit_id' => $product['unit_id'] ?? null,
                'quantity' => $product['quantity'],
                'unit_price' => $product['unit_price'],
                'total' => $product['total'],
            ]);

            // Record product movement
            ProductMovement::recordMovement(
                $product['product_id'],
                ProductMovement::TYPE_PURCHASE_RETURN,
                -$product['quantity'],
                'ProductReleaseNote-' . $productReleaseNote->id
            );

            // Use unit from PRN product instead of PTR
            $productModel = Product::find($product['product_id']);
            
            if ($productModel) {
                $quantity = (float)$product['quantity'];
                $unitId = $product['unit_id'] ?? null;
                $purchaseToTransfer = (float)($productModel->purchase_to_transfer_rate ?: 1.0);
                $transferToSales = (float)($productModel->transfer_to_sales_rate ?: 1.0);

                // Smart deduction based on which unit was used in transfer
                if ($unitId == $productModel->purchase_unit_id) {
                    // Transfer in purchase units (e.g., 2 boxes)
                    $productModel->decrement('store_quantity_in_purchase_unit', $quantity);
                    // Also reduce transfer units proportionally
                    $productModel->decrement('store_quantity_in_transfer_unit', $quantity * $purchaseToTransfer);
                    // Add to shop in sales units
                    $quantityInSalesUnits = round($quantity * $purchaseToTransfer * $transferToSales, 4);
                    $productModel->increment('shop_quantity_in_sales_unit', $quantityInSalesUnits);
                    
                } elseif ($unitId == $productModel->transfer_unit_id) {
                    // Transfer in transfer units (e.g., 4 bundles)
                    $currentTransferQty = $productModel->store_quantity_in_transfer_unit;
                    
                    if ($currentTransferQty >= $quantity) {
                        // Enough bundles available, just deduct
                        $productModel->decrement('store_quantity_in_transfer_unit', $quantity);
                    } else {
                        // Need to break down from boxes
                        $needed = $quantity - $currentTransferQty;
                        $boxesToBreak = ceil($needed / $purchaseToTransfer);
                        
                        // Break boxes into bundles
                        $productModel->decrement('store_quantity_in_purchase_unit', $boxesToBreak);
                        $newBundles = $boxesToBreak * $purchaseToTransfer;
                        $productModel->increment('store_quantity_in_transfer_unit', $newBundles);
                        
                        // Now deduct the needed bundles
                        $productModel->decrement('store_quantity_in_transfer_unit', $quantity);
                    }
                    
                    // Add to shop in sales units
                    $quantityInSalesUnits = round($quantity * $transferToSales, 4);
                    $productModel->increment('shop_quantity_in_sales_unit', $quantityInSalesUnits);
                    
                } elseif ($unitId == $productModel->sales_unit_id) {
                    // Transfer in sales units (e.g., 50 bottles) - need to break from bundles/boxes
                    $quantityNeeded = $quantity;
                    
                    // Try to get from bundles first
                    $currentBundles = $productModel->store_quantity_in_transfer_unit;
                    $bottlesFromBundles = $currentBundles * $transferToSales;
                    
                    if ($bottlesFromBundles >= $quantityNeeded) {
                        // Enough in bundles
                        $bundlesNeeded = ceil($quantityNeeded / $transferToSales);
                        $productModel->decrement('store_quantity_in_transfer_unit', $bundlesNeeded);
                    } else {
                        // Need to break from boxes
                        $boxesToBreak = ceil($quantityNeeded / ($purchaseToTransfer * $transferToSales));
                        
                        // Break boxes into bundles
                        $productModel->decrement('store_quantity_in_purchase_unit', $boxesToBreak);
                        $newBundles = $boxesToBreak * $purchaseToTransfer;
                        $productModel->increment('store_quantity_in_transfer_unit', $newBundles);
                        
                        // Now deduct from bundles
                        $bundlesNeeded = ceil($quantityNeeded / $transferToSales);
                        $productModel->decrement('store_quantity_in_transfer_unit', $bundlesNeeded);
                    }
                    
                    // Add to shop
                    $productModel->increment('shop_quantity_in_sales_unit', $quantity);
                }
                
                $productModel->save();
            }
        }

        // Update the related PTR status to 'completed'
        $productTransferRequest = ProductTransferRequest::find($validated['product_transfer_request_id']);
        if ($productTransferRequest) {
            $productTransferRequest->update(['status' => 'completed']);
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
                    // undo: increment store_quantity_in_purchase_unit (back to storage), decrement shop_quantity_in_sales_unit (remove from shop)
                    $productModel->increment('store_quantity_in_purchase_unit', $quantityInPurchaseUnits);
                    $productModel->decrement('shop_quantity_in_sales_unit', $quantityInSalesUnits);
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
                if ($product) {
                    $quantity = is_numeric($product['quantity']) ? (float)$product['quantity'] : floatval($product['quantity']);
                    $purchaseToTransfer = is_numeric($product->purchase_to_transfer_rate) && $product->purchase_to_transfer_rate > 0 ? (float)$product->purchase_to_transfer_rate : 1.0;
                    $transferToSales = is_numeric($product->transfer_to_sales_rate) && $product->transfer_to_sales_rate > 0 ? (float)$product->transfer_to_sales_rate : 1.0;
                    
                    $quantityInPurchaseUnits = $quantity;
                    $quantityInSalesUnits = round($quantity * $purchaseToTransfer * $transferToSales, 4);
                    
                    $product->decrement('store_quantity_in_purchase_unit', $quantityInPurchaseUnits);
                    $product->increment('shop_quantity_in_sales_unit', $quantityInSalesUnits);
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
                    
                    $quantityInPurchaseUnits = $quantity;
                    $quantityInSalesUnits = round($quantity * $purchaseToTransfer * $transferToSales, 4);
                    
                    // restore: increment store_quantity_in_purchase_unit, decrement shop_quantity_in_sales_unit
                    $product->increment('store_quantity_in_purchase_unit', $quantityInPurchaseUnits);
                    $product->decrement('shop_quantity_in_sales_unit', $quantityInSalesUnits);
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
