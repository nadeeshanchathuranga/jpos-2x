<?php

namespace App\Http\Controllers;

use App\Models\ProductTransferRequest;
use App\Models\ProductTransferRequestProduct;
use App\Models\ProductReleaseNote;
use App\Models\ProductReleaseNoteProduct;
use App\Models\Product;
use App\Models\MeasurementUnit;
use App\Models\ShopStockByUnit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProductTransferRequestsController extends Controller
{
    public function index()
    {
        $productTransferRequests = ProductTransferRequest::with(['user', 'product_transfer_request_products.product', 'product_transfer_request_products.measurement_unit'])
            ->paginate(10);
            
        // Load products with their assigned units
        $products = Product::with(['purchaseUnit', 'transferUnit', 'salesUnit'])->get();
        
        $measurementUnits = MeasurementUnit::where('status', '!=', 0)->get();
        $users = User::all();
        $transferNo = 'PTR-' . date('YmdHis');

        return Inertia::render('ProductTransferRequests/Index', [
            'productTransferRequests' => $productTransferRequests,
            'products' => $products,
            'measurementUnits' => $measurementUnits,
            'users' => $users,
            'product_transfer_request_no' => $transferNo
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_transfer_request_no' => 'required|string|unique:product_transfer_requests,product_transfer_request_no',
            'request_date' => 'required|date',
            'user_id' => 'required|exists:users,id',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.requested_quantity' => 'required|integer|min:1',
            'products.*.unit_id' => 'nullable|exists:measurement_units,id'
        ]);

        DB::beginTransaction();

        try {
            // Determine initial status: admins' requests are auto-approved
            $status = (Auth::user() && (int)Auth::user()->role === 0) ? 'approved' : 'pending';

            $productTransferRequest = ProductTransferRequest::create([
                'product_transfer_request_no' => $validated['product_transfer_request_no'],
                'request_date' => $validated['request_date'],
                'user_id' => $validated['user_id'],
                'status' => $status,
            ]);

            foreach ($validated['products'] as $productData) {
                ProductTransferRequestProduct::create([
                    'product_transfer_request_id' => $productTransferRequest->id,
                    'product_id' => $productData['product_id'],
                    'requested_quantity' => $productData['requested_quantity'],
                    'unit_id' => $productData['unit_id'] ?? null
                ]);
            }

            // If admin auto-approved, auto-generate PRN and transfer stock
            if ($status === 'approved') {
                // Create PRN (Product Release Note)
                $prn = \App\Models\ProductReleaseNote::create([
                    'product_transfer_request_id' => $productTransferRequest->id,
                    'user_id' => Auth::id(),
                    'release_date' => now()->toDateString(),
                    'status' => 1, // Released
                    'remark' => 'Auto-generated from PTR approval',
                ]);

                // Create PRN products and transfer stock
                foreach ($validated['products'] as $productData) {
                    $product = Product::find($productData['product_id']);

                    
                    if ($product) {
                        $quantity = (float)$productData['requested_quantity'];
                        $unitId = $productData['unit_id'] ?? null;
                        
                        $purchaseToTransferRate = $product->purchase_to_transfer_rate ?? 1;
                        $transferToSalesRate = $product->transfer_to_sales_rate ?? 1;

                        
                        // Convert requested quantity to bundles (transfer units)
                        $quantityInBundles = $quantity;
                        if ($unitId == $product->purchase_unit_id) {
                            $quantityInBundles = $quantity * $purchaseToTransferRate;
                        } elseif ($unitId == $product->sales_unit_id) {
                            $quantityInBundles = $transferToSalesRate > 0 ? $quantity / $transferToSalesRate : 0;
                        }

                        
                        // Get current store quantities (raw DB values) - query directly to avoid accessor calculations
                        $dbRecord = DB::table('products')->where('id', $product->id)->first();
                        $currentBoxes = $dbRecord->store_quantity_in_purchase_unit ?? 0;
                        $currentLooseBundles = $dbRecord->store_quantity_in_transfer_unit ?? 0;
                        // Total available bundles
                        $totalAvailableBundles = ($currentBoxes * $purchaseToTransferRate) + $currentLooseBundles;
                        
                        if ($totalAvailableBundles < $quantityInBundles) {
                            DB::rollBack();
                            return back()->withErrors([
                                'error' => "Insufficient store quantity for product: {$product->name}. Available: {$totalAvailableBundles} bundles, Required: {$quantityInBundles} bundles"
                            ]);
                        }
                        
                        // Calculate remaining after deduction
                        $remainingBundles = $totalAvailableBundles - $quantityInBundles;
                        
                        // Calculate new boxes and loose bundles
                        $newBoxes = floor($remainingBundles / $purchaseToTransferRate);
                        $newLooseBundles = $remainingBundles - ($newBoxes * $purchaseToTransferRate);
                        
                        // Update store quantities using direct DB queries
                        $purchaseDifference = $currentBoxes - $newBoxes;
                        $looseDifference = $currentLooseBundles - $newLooseBundles;
                        
                        if ($purchaseDifference != 0) {
                            if ($purchaseDifference > 0) {
                                DB::table('products')
                                    ->where('id', $product->id)
                                    ->decrement('store_quantity_in_purchase_unit', $purchaseDifference);
                            } else {
                                DB::table('products')
                                    ->where('id', $product->id)
                                    ->increment('store_quantity_in_purchase_unit', abs($purchaseDifference));
                            }
                        }
                        
                        if ($looseDifference != 0) {
                            if ($looseDifference > 0) {
                                DB::table('products')
                                    ->where('id', $product->id)
                                    ->decrement('store_quantity_in_transfer_unit', $looseDifference);
                            } else {
                                DB::table('products')
                                    ->where('id', $product->id)
                                    ->increment('store_quantity_in_transfer_unit', abs($looseDifference));
                            }
                        }
                        
                        // Convert to sales units for shop and update
                        $quantityInSalesUnits = $quantityInBundles * $transferToSalesRate;
                        DB::table('products')
                            ->where('id', $product->id)
                            ->increment('shop_quantity_in_sales_unit', $quantityInSalesUnits);
                        
                        // Track shop stock by specific unit for accurate returns
                        ShopStockByUnit::addStock(
                            $productData['product_id'],
                            $unitId,
                            $quantity
                        );
                        
                        // Deduct from product_available_quantities table using FIFO (oldest GRN first)
                        // This ensures we release stock based on the order received (FIFO)
                        $availableQuantities = \App\Models\ProductAvailableQuantity::where('product_id', $product->id)
                            ->orderBy('goods_received_note_id', 'asc') // FIFO by GRN
                            ->orderBy('created_at', 'asc') // Then by creation date within same GRN
                            ->get();
                        
                        // Convert requested quantity to base units (bundles) for smart deduction
                        // This allows us to intelligently convert boxes to bundles if needed
                        $quantityInBundles = 0;
                        
                        if ($unitId == $product->purchase_unit_id) {
                            // Purchase unit selected: X boxes = X * purchase_to_transfer_rate bundles
                            $quantityInBundles = $quantity * $purchaseToTransferRate;
                        } elseif ($unitId == $product->transfer_unit_id) {
                            // Transfer unit selected: already in bundles
                            $quantityInBundles = $quantity;
                        } elseif ($unitId == $product->sales_unit_id) {
                            // Sales unit selected: X bottles = X / transfer_to_sales_rate bundles
                            $quantityInBundles = $transferToSalesRate > 0 ? $quantity / $transferToSalesRate : 0;
                        } else {
                            // Default to bundles if unit not specified
                            $quantityInBundles = $quantity;
                        }
                        
                        // Track remaining quantity to deduct in bundles
                        $remainingBundlesToDeduct = $quantityInBundles;
                        
                        // FIFO deduction from available quantities (across multiple GRNs)
                        // Smart conversion: intelligently break down boxes into bundles and handle fractional amounts
                        foreach ($availableQuantities as $availableQty) {
                            // Stop if all quantities have been deducted
                            if ($remainingBundlesToDeduct <= 0) {
                                break;
                            }
                            
                            // Convert available inventory to total bundles (with fractional precision)
                            $totalBundlesInBatch = ((int)$availableQty->available_quantity * $purchaseToTransferRate) +
                                                    (int)$availableQty->quantity_in_transfer_unit +
                                                    ((int)$availableQty->quantity_in_sales_unit / $transferToSalesRate);
                            
                            if ($totalBundlesInBatch <= 0) {
                                continue; // Skip empty batches
                            }
                            
                            // Deduct from this batch
                            $bundlesToDeductFromBatch = min($remainingBundlesToDeduct, $totalBundlesInBatch);
                            $remainingBundlesToDeduct -= $bundlesToDeductFromBatch;
                            
                            // Step 1: Deduct sales units first (if any fractional bundles)
                            // Calculate how many sales units are needed from this deduction
                            $neededSalesUnits = (int)($bundlesToDeductFromBatch * $transferToSalesRate);
                            
                            // Deduct from available sales units first
                            $salesToDeductFromAvailable = min($neededSalesUnits, (int)$availableQty->quantity_in_sales_unit);
                            if ($salesToDeductFromAvailable > 0) {
                                $availableQty->quantity_in_sales_unit = max(0, (int)$availableQty->quantity_in_sales_unit - $salesToDeductFromAvailable);
                                $neededSalesUnits -= $salesToDeductFromAvailable;
                                $bundlesToDeductFromBatch -= ($salesToDeductFromAvailable / $transferToSalesRate);
                            }
                            
                            // If still need sales units, break down transfer units
                            if ($neededSalesUnits > 0 && (int)$availableQty->quantity_in_transfer_unit > 0) {
                                $transferUnitsToBreakDown = ceil($neededSalesUnits / $transferToSalesRate);
                                $transferUnitsToBreakDown = min($transferUnitsToBreakDown, (int)$availableQty->quantity_in_transfer_unit);
                                
                                if ($transferUnitsToBreakDown > 0) {
                                    // Break down transfer units to sales units
                                    $salesUnitsFromTransfer = $transferUnitsToBreakDown * $transferToSalesRate;
                                    $availableQty->quantity_in_transfer_unit = max(0, (int)$availableQty->quantity_in_transfer_unit - $transferUnitsToBreakDown);
                                    
                                    // Deduct what we need, store the rest
                                    $salesToDeductFromConverted = min($neededSalesUnits, $salesUnitsFromTransfer);
                                    $remainingSalesUnits = $salesUnitsFromTransfer - $salesToDeductFromConverted;
                                    
                                    $availableQty->quantity_in_sales_unit = (int)$availableQty->quantity_in_sales_unit + $remainingSalesUnits;
                                    $bundlesToDeductFromBatch -= ($salesToDeductFromConverted / $transferToSalesRate);
                                    $neededSalesUnits -= $salesToDeductFromConverted;
                                }
                            }
                            
                            // Step 2: Deduct loose bundles
                            $bundlesFromLoose = min((int)$bundlesToDeductFromBatch, (int)$availableQty->quantity_in_transfer_unit);
                            if ($bundlesFromLoose > 0) {
                                $availableQty->quantity_in_transfer_unit = max(0, (int)$availableQty->quantity_in_transfer_unit - $bundlesFromLoose);
                                $bundlesToDeductFromBatch -= $bundlesFromLoose;
                            }
                            
                            // Step 3: Deduct from boxes (may need to break down boxes into bundles/sales units)
                            if ($bundlesToDeductFromBatch > 0 && (int)$availableQty->available_quantity > 0) {
                                // How many whole boxes to deduct
                                $wholeBoxesToDeduct = floor($bundlesToDeductFromBatch / $purchaseToTransferRate);
                                $boxesFromBatch = min($wholeBoxesToDeduct, (int)$availableQty->available_quantity);
                                
                                if ($boxesFromBatch > 0) {
                                    $availableQty->available_quantity = max(0, (int)$availableQty->available_quantity - $boxesFromBatch);
                                    $bundlesToDeductFromBatch -= ($boxesFromBatch * $purchaseToTransferRate);
                                }
                                
                                // If still need more bundles, break down an additional box
                                if ($bundlesToDeductFromBatch > 0 && (int)$availableQty->available_quantity > 0) {
                                    $availableQty->available_quantity = max(0, (int)$availableQty->available_quantity - 1);
                                    
                                    // Break box into bundles and potentially sales units
                                    $remainingBundles = $purchaseToTransferRate - $bundlesToDeductFromBatch;
                                    
                                    // Store whole bundles in quantity_in_transfer_unit
                                    $wholeBundles = floor($remainingBundles);
                                    $availableQty->quantity_in_transfer_unit = (int)$availableQty->quantity_in_transfer_unit + (int)$wholeBundles;
                                    
                                    // Store fractional bundles as sales units
                                    $fractionalBundles = $remainingBundles - $wholeBundles;
                                    $remainingSalesUnits = (int)round($fractionalBundles * $transferToSalesRate);
                                    if ($remainingSalesUnits > 0) {
                                        $availableQty->quantity_in_sales_unit = (int)$availableQty->quantity_in_sales_unit + $remainingSalesUnits;
                                    }
                                    
                                    $bundlesToDeductFromBatch = 0;
                                }
                            }
                            
                            // Delete batch if fully depleted across all units
                            if ($availableQty->available_quantity <= 0 && $availableQty->quantity_in_transfer_unit <= 0 && $availableQty->quantity_in_sales_unit <= 0) {
                                $availableQty->delete();
                            } else {
                                $availableQty->save();
                            }
                        }
                        
                        // Create PRN product record
                        // Get unit price based on the selected unit
                        $unitPrice = 0;
                        
                        if ($unitId == $product->purchase_unit_id) {
                            // Purchase unit - get purchase price from GRN
                            $unitPrice = DB::table('goods_received_notes_products')
                                ->where('product_id', $productData['product_id'])
                                ->latest('created_at')
                                ->value('purchase_price') ?? $product->purchase_price ?? 0;
                        } elseif ($unitId == $product->transfer_unit_id) {
                            // Transfer unit - calculate as purchase_price / purchase_to_transfer_rate
                            $purchasePriceFromGrn = DB::table('goods_received_notes_products')
                                ->where('product_id', $productData['product_id'])
                                ->latest('created_at')
                                ->value('purchase_price') ?? $product->purchase_price ?? 0;
                            $unitPrice = ((float)$purchasePriceFromGrn) / ((float)($product->purchase_to_transfer_rate ?? 1));
                        } elseif ($unitId == $product->sales_unit_id) {
                            // Sales unit - use retail/sales price
                            $unitPrice = $product->retail_price ?? $product->sales_price ?? 0;
                        } else {
                            // Default to purchase price
                            $unitPrice = DB::table('goods_received_notes_products')
                                ->where('product_id', $productData['product_id'])
                                ->latest('created_at')
                                ->value('purchase_price') ?? $product->purchase_price ?? 0;
                        }
                        
                        \App\Models\ProductReleaseNoteProduct::create([
                            'product_release_note_id' => $prn->id,
                            'product_id' => $productData['product_id'],
                            'unit_id' => $unitId,
                            'quantity' => $quantity,
                            'unit_price' => (float)$unitPrice,
                            'total' => $quantity * ((float)$unitPrice),
                        ]);
                    }
                }
                
                // Mark PTR as completed since PRN was generated
                $productTransferRequest->update(['status' => 'completed']);
            }

            DB::commit();

            return redirect()->route('product-transfer-requests.index')
                ->with('success', 'Purchase Order Request created successfully' . ($status === 'approved' ? ' and PRN auto-generated' : ''));

        } catch (\Exception $e) {


            DB::rollBack();

            return back()->withErrors([
                'error' => 'Failed to create PTR: ' . $e->getMessage()
            ]);
        }
    }

    public function update(Request $request, ProductTransferRequest $productTransferRequest)
    {
        if ($productTransferRequest->status !== 'pending') {
            return redirect()->back()->with('error', 'Only pending orders can be edited');
        }
        $validated = $request->validate([
            'product_transfer_request_no' => 'required|string|unique:product_transfer_requests,product_transfer_request_no,' . $productTransferRequest->id,
            'request_date' => 'required|date',
            'user_id' => 'required|exists:users,id',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.requested_quantity' => 'required|numeric|min:1',
            'products.*.unit_id' => 'nullable|exists:measurement_units,id'
        ]);

        DB::beginTransaction();
        try {
            $productTransferRequest->update([
                'product_transfer_request_no' => $validated['product_transfer_request_no'],
                'request_date' => $validated['request_date'],
                'user_id' => $validated['user_id']
            ]);

            // Remove old products and insert new ones
            ProductTransferRequestProduct::where('product_transfer_request_id', $productTransferRequest->id)->delete();

            foreach ($validated['products'] as $product) {
                ProductTransferRequestProduct::create([
                    'product_transfer_request_id' => $productTransferRequest->id,
                    'product_id' => $product['product_id'],
                    'requested_quantity' => $product['requested_quantity'],
                    'unit_id' => $product['unit_id'] ?? null
                ]);
            }

            DB::commit();
            return redirect()->route('product-transfer-requests.index')->with('success', 'Purchase Order Request updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors([
                'error' => 'Failed to update PTR: ' . $e->getMessage()
            ]);
        }
    }

    public function destroy(ProductTransferRequest $productTransferRequest)
    {
        if ($productTransferRequest->status !== 'pending') {
            return redirect()->back()->with('error', 'Only pending orders can be deleted');
        }

        $productTransferRequest->delete();
        return redirect()->route('product-transfer-requests.index')->with('success', 'Purchase Order Request deleted successfully');
    }

    public function updateStatus(Request $request, ProductTransferRequest $productTransferRequest)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected,completed'
        ]);

        // Only admin (role === 0) may update the status
        if (!(Auth::user() && (int) Auth::user()->role === 0)) {
            return back()->withErrors(['error' => 'Unauthorized. Only admin users can change the status.']);
        }

        // Prevent any changes if the PTR is already approved
        if ($productTransferRequest->status === 'approved') {
            return back()->withErrors(['error' => 'Cannot change status of an already approved request.']);
        }

        DB::beginTransaction();

        try {
            $oldStatus = $productTransferRequest->status;
            $newStatus = $request->status;

            // Note: Stock is NOT transferred here.
            // Stock movement only happens when PRN (Product Release Note) is created.
            // PTR approval is just an authorization, not actual goods movement.

            $productTransferRequest->update(['status' => $newStatus]);

            DB::commit();

            return back()->with('success', 'Status updated successfully and stock transferred');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to update status: ' . $e->getMessage()]);
        }
    }


    public function productTransferRequestDetails($id)
    {
        try {
            // Load the Product Transfer Request
            $productTransferRequest = ProductTransferRequest::with(['product_transfer_request_products.product', 'user'])
                ->findOrFail($id);

        // Get products from product_transfer_request_products table
        $productTransferRequestProducts = ProductTransferRequestProduct::where('product_transfer_request_id', $id)
            ->with(['product', 'measurement_unit', 'product.purchaseUnit', 'product.transferUnit', 'product.salesUnit'])
            ->get()
            ->map(function($productTransferRequestProduct) {
                $product = $productTransferRequestProduct->product;
                
                // Get measurement unit from product_transfer_request_products table first
                $unitName = 'N/A';
                $measurement_unit = null;
                
                if ($productTransferRequestProduct->measurement_unit) {
                    $unitName = $productTransferRequestProduct->measurement_unit->name;
                    $measurement_unit = $productTransferRequestProduct->measurement_unit;
                }

                // Prefer a transfer price if available, otherwise use retail price, or fallback to 0
                $price = $product->transfer_price
                    ?? $product->retail_price
                    ?? 0;

                // Fetch purchase price from goods_received_note_products table (latest/most recent)
                $purchasePrice = DB::table('goods_received_notes_products')
                    ->where('product_id', $productTransferRequestProduct->product_id)
                    ->latest('created_at')
                    ->value('purchase_price') ?? $product->purchase_price ?? 0;

                return [
                    'product_id' => $productTransferRequestProduct->product_id,
                    'name'       => $product->name ?? 'N/A',
                    'qty'        => $productTransferRequestProduct->requested_quantity ?? 0,
                    'price'      => (float) $price,
                    'purchase_price' => (float) $purchasePrice,
                    'unit'       => $unitName,
                    'unit_id'    => $productTransferRequestProduct->unit_id,
                    'measurement_unit' => $measurement_unit,
                    'purchase_unit' => $product->purchaseUnit,
                    'transfer_unit' => $product->transferUnit,
                    'sales_unit' => $product->salesUnit,
                    'purchase_to_transfer_rate' => $product->purchase_to_transfer_rate ?? 1,
                    'transfer_to_sales_rate' => $product->transfer_to_sales_rate ?? 1,
                ];
            });




        return response()->json([
            'productTransferRequest' => $productTransferRequest,
            'productTransferRequestProducts' => $productTransferRequestProducts
        ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load PTR details',
                'message' => $e->getMessage()
            ], 404);
        }
    }
}
