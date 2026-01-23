<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GoodsReceivedNoteReturn;
use App\Models\GoodsReceivedNoteReturnProduct;
use App\Models\GoodsReceivedNote;
use App\Models\GoodsReceivedNoteProduct;
use App\Models\Product;
use App\Models\ProductMovement;
use App\Models\MeasurementUnit;
use App\Models\CompanyInformation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

/**
 * GoodReceiveNoteReturnController
 * 
 * Manages the return of goods to suppliers (BRN - Bill Return Note).
 * Handles the complete lifecycle of GRN returns including:
 * - Recording returned items with quantities and remarks
 * - Inventory adjustment (decrementing stock when goods are returned)
 * - Product movement tracking for audit trail
 * - Unit conversion for proper stock accounting
 * 
 * Business Logic:
 * - Returns reference an original GRN (Goods Received Note)
 * - Stock is decremented when returns are created
 * - Stock is restored when returns are deleted
 * - All operations are wrapped in database transactions
 * 
 * @package App\Http\Controllers
 */
class GoodReceiveNoteReturnController extends Controller
{
    /**
     * Display a listing of all GRN returns
     * 
     * Provides paginated list of returns with:
     * - User who processed the return
     * - Original GRN reference and its products
     * - Returned products with quantities
     * - Available GRNs for creating new returns (only active GRNs)
     * 
     * @return \Inertia\Response
     */
    public function index()
{
    // Eager-load all necessary relationships
    $returns = GoodsReceivedNoteReturn::with([
        'user',
        'goodsReceivedNote.goods_received_note_products.product.measurement_unit',
        'goodsReceivedNoteReturnProducts.product.measurement_unit'
    ])->latest()->paginate(20);
    
    // Eager-load GRN products for autofill on selection
    // Only show active GRNs (status != 0)
    $goodsReceivedNotes = GoodsReceivedNote::with([
        'goods_received_note_products.product.measurement_unit'
    ])
        ->where('status', '!=', 0)
        ->orderByDesc('id')
        ->get()
        ->toArray();
    
    // Get authenticated user for default assignment
    $user = auth()->user();
    
    $currencySymbol = CompanyInformation::first();
    
    // Load available products and measurement units
    // Only active products (status != 0) can be returned
    $availableProducts = Product::where('status', '!=', 0)
        ->with([
            'measurement_unit',
            'purchaseUnit',
            'transferUnit',
            'salesUnit'
        ])
        ->orderBy('name')
        ->get();
    
    // Get all measurement units
    $measurementUnits = MeasurementUnit::orderBy('name')->get()->toArray();
  

    return Inertia::render('GoodsReceivedNoteReturns/Index', [
        'returns' => $returns,
        'goodsReceivedNotes' => $goodsReceivedNotes,
        // Alias for frontend prop name `grns`
        'grns' => $goodsReceivedNotes,
        'user' => $user,
        'availableProducts' => $availableProducts,
        'measurementUnits' => $measurementUnits,
        'currencySymbol' => $currencySymbol,
    ]);
}
    /**
     * Show the form for creating a new GRN return
     * 
     * Provides necessary data for return creation:
     * - Available GRNs with their products (for autofill)
     * - All products (for manual selection)
     * - Measurement units (for display)
     * - Current user (for default assignment)
     * 
     * @return \Inertia\Response
     */
    public function create()
    {
        // Include goods_received_note_products so frontend can autofill products without extra routes
        // Serialize to plain array for predictable client-side shape
        // Only show active GRNs (status != 0)
        $goodsReceivedNotes = GoodsReceivedNote::with([
            'goods_received_note_products.product.measurement_unit',
            'goods_received_note_products.product.purchaseUnit',
            'goods_received_note_products.product.transferUnit',
            'goods_received_note_products.product.salesUnit'
        ])
            ->where('status', '!=', 0)
            ->orderByDesc('id')
            ->get()
            ->toArray();
        
        // Load all products for manual product selection with unit relationships
        $products = Product::with([
            'purchaseUnit',
            'transferUnit',
            'salesUnit'
        ])->orderBy('name')->get();
        
        // Load measurement units for display purposes
        $measurementUnits = MeasurementUnit::orderBy('name')->get()->toArray();
        
        // Get authenticated user for default assignment
        $user = auth()->user();
        return Inertia::render('goodsReceivedNoteReturns/Create',[ 
        'goodsReceivedNotes' => $goodsReceivedNotes,
        'products' => $products,
        'measurementUnits' => $measurementUnits,
        'user' => $user,
        ]);
    }

    /**
     * Store a newly created GRN return
     * 
     * Process flow:
     * 1. Validates return data (GRN reference, products, quantities)
     * 2. Creates GRN return record
     * 3. Creates return product line items
     * 4. Records product movements for audit trail (Type 5: GRN_RETURN)
     * 5. Adjusts inventory:
     *    - Increments store_quantity (returns go back to store)
     *    - Applies unit conversion rates (purchase → transfer → sales)
     * 
     * All operations wrapped in transaction for data consistency.
     * 
     * @param Request $request - Contains goods_received_note_id, date, user_id, and products array
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validate incoming data
        $validated = $request->validate([
            'goods_received_note_id' => 'required|exists:goods_received_notes,id',
            'date' => 'required|date',
            'user_id' => 'required|exists:users,id',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.qty' => 'required|numeric|min:1',
            'products.*.unit_id' => 'required|exists:measurement_units,id',
            'products.*.remarks' => 'nullable|string',
        ]);

        // Start database transaction to ensure data consistency
        DB::beginTransaction();
        try {
            // Create the main GRN return record
            $grnReturn = GoodsReceivedNoteReturn::create([
                'goods_received_note_id' => $validated['goods_received_note_id'],
                'date' => $validated['date'],
                'user_id' => $validated['user_id'],
            ]);

            // Track total return amount for GRN subtotal update
            $totalReturnAmount = 0;

            // Process each returned product
            foreach ($validated['products'] as $p) {
                // Create return product line item
                GoodsReceivedNoteReturnProduct::create([
                    'goods_received_note_return_id' => $grnReturn->id,
                    'product_id' => $p['product_id'],
                    'quantity' => $p['qty'],
                    'measurement_unit_id' => $p['unit_id'],
                    'remarks' => $p['remarks'] ?? null,
                ]);
                
                // Record product movement for GRN return (Type 5: TYPE_GRN_RETURN)
                // This creates an audit trail for inventory tracking
                if (!empty($p['qty']) && $p['qty'] > 0) {
                    ProductMovement::record($p['product_id'], ProductMovement::TYPE_GRN_RETURN, $p['qty'], 'GRN Return #' . $grnReturn->id);
                    
                    // Get the GRN product record to retrieve the purchase price
                    $grnProduct = GoodsReceivedNoteProduct::where('goods_received_note_id', $validated['goods_received_note_id'])
                        ->where('product_id', $p['product_id'])
                        ->first();
                    
                    if ($grnProduct) {
                        // Deduct returned quantity from the original GRN product quantity
                        $grnProduct->decrement('quantity', $p['qty']);
                        
                        // Get purchase price from GRN product
                        $purchasePrice = (float)($grnProduct->purchase_price ?? 0);
                        
                        // Update store inventory based on unit type returned
                        $returnedQty = is_numeric($p['qty']) ? (float)$p['qty'] : floatval($p['qty']);
                        $selectedUnitId = (int)$p['unit_id'];
                        
                        // Get product with fresh data from DB for unit conversion rates
                        $prod = Product::find($p['product_id']);
                        if ($prod) {
                            $purchaseUnitId = (int)$prod->purchase_unit_id;
                            $transferUnitId = (int)$prod->transfer_unit_id;
                            $salesUnitId = (int)$prod->sales_unit_id;
                            
                            $purchaseToTransferRate = (float)$prod->purchase_to_transfer_rate ?: 1.0;
                            $transferToSalesRate = (float)$prod->transfer_to_sales_rate ?: 1.0;
                            
                            // Calculate return amount based on unit type
                            $returnAmount = 0;
                            if ($selectedUnitId == $purchaseUnitId) {
                                // Returned in purchase units
                                $returnAmount = $returnedQty * $purchasePrice;
                            } elseif ($selectedUnitId == $transferUnitId) {
                                // Returned in transfer units - convert to purchase unit price
                                $transferPrice = $purchasePrice / $purchaseToTransferRate;
                                $returnAmount = $returnedQty * $transferPrice;
                            } elseif ($selectedUnitId == $salesUnitId) {
                                // Returned in sales units - convert to purchase unit price
                                $salesPrice = $purchasePrice / ($purchaseToTransferRate * $transferToSalesRate);
                                $returnAmount = $returnedQty * $salesPrice;
                            }
                            
                            // Add to total return amount
                            $totalReturnAmount += $returnAmount;
                            
                            // Update the total in goods_received_note_products
                            $grnProduct->decrement('total', $returnAmount);
                            
                            // Read raw database values directly to avoid accessor calculations
                            $dbRecord = DB::table('products')->where('id', $p['product_id'])->first();
                            $currentPurchaseQty = (float)($dbRecord->store_quantity_in_purchase_unit ?? 0);
                            $currentLooseQty = (float)($dbRecord->store_quantity_in_transfer_unit ?? 0);
                            
                            if ($selectedUnitId == $purchaseUnitId) {
                                // Returned in purchase units - decrement directly
                                DB::table('products')
                                    ->where('id', $p['product_id'])
                                    ->decrement('store_quantity_in_purchase_unit', $returnedQty);
                            } elseif ($selectedUnitId == $transferUnitId) {
                                // Returned in transfer units - convert and redistribute
                                // Calculate total transfer units
                                $totalTransferUnits = ($currentPurchaseQty * $purchaseToTransferRate) + $currentLooseQty;
                                
                                // Subtract returned quantity
                                $newTotalTransferUnits = $totalTransferUnits - $returnedQty;
                                
                                // Re-distribute into purchase units and loose bundles
                                $newPurchaseQty = floor($newTotalTransferUnits / $purchaseToTransferRate);
                                $newLooseQty = $newTotalTransferUnits % $purchaseToTransferRate;
                                
                                // Update using raw DB queries
                                $purchaseDifference = $currentPurchaseQty - $newPurchaseQty;
                                $looseDifference = $currentLooseQty - $newLooseQty;
                                
                                if ($purchaseDifference != 0) {
                                    if ($purchaseDifference > 0) {
                                        DB::table('products')
                                            ->where('id', $p['product_id'])
                                            ->decrement('store_quantity_in_purchase_unit', $purchaseDifference);
                                    } else {
                                        DB::table('products')
                                            ->where('id', $p['product_id'])
                                            ->increment('store_quantity_in_purchase_unit', abs($purchaseDifference));
                                    }
                                }
                                
                                if ($looseDifference != 0) {
                                    if ($looseDifference > 0) {
                                        DB::table('products')
                                            ->where('id', $p['product_id'])
                                            ->decrement('store_quantity_in_transfer_unit', $looseDifference);
                                    } else {
                                        DB::table('products')
                                            ->where('id', $p['product_id'])
                                            ->increment('store_quantity_in_transfer_unit', abs($looseDifference));
                                    }
                                }
                            } elseif ($selectedUnitId == $salesUnitId) {
                                // Returned in sales units - convert to transfer units first, then redistribute
                                $returnedInTransferUnits = $returnedQty / $transferToSalesRate;
                                
                                // Use the already-read values from above
                                // Calculate total transfer units
                                $totalTransferUnits = ($currentPurchaseQty * $purchaseToTransferRate) + $currentLooseQty;
                                
                                // Subtract returned quantity (converted to transfer units)
                                $newTotalTransferUnits = $totalTransferUnits - $returnedInTransferUnits;
                                
                                // Re-distribute into purchase units and loose bundles
                                $newPurchaseQty = floor($newTotalTransferUnits / $purchaseToTransferRate);
                                $newLooseQty = $newTotalTransferUnits % $purchaseToTransferRate;
                                
                                // Update using raw DB queries
                                $purchaseDifference = $currentPurchaseQty - $newPurchaseQty;
                                $looseDifference = $currentLooseQty - $newLooseQty;
                                
                                if ($purchaseDifference != 0) {
                                    if ($purchaseDifference > 0) {
                                        DB::table('products')
                                            ->where('id', $p['product_id'])
                                            ->decrement('store_quantity_in_purchase_unit', $purchaseDifference);
                                    } else {
                                        DB::table('products')
                                            ->where('id', $p['product_id'])
                                            ->increment('store_quantity_in_purchase_unit', abs($purchaseDifference));
                                    }
                                }
                                
                                if ($looseDifference != 0) {
                                    if ($looseDifference > 0) {
                                        DB::table('products')
                                            ->where('id', $p['product_id'])
                                            ->decrement('store_quantity_in_transfer_unit', $looseDifference);
                                    } else {
                                        DB::table('products')
                                            ->where('id', $p['product_id'])
                                            ->increment('store_quantity_in_transfer_unit', abs($looseDifference));
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // Update GRN subtotal by deducting the total return amount
            if ($totalReturnAmount > 0) {
                GoodsReceivedNote::where('id', $validated['goods_received_note_id'])
                    ->decrement('subtotal', $totalReturnAmount);
            }

            DB::commit();
            return redirect()->route('good-receive-note-returns.index')->with('success', 'GRN return recorded.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified GRN return from storage
     * 
     * Deletion process:
     * 1. Restores stock levels (increments store_quantity)
     * 2. Removes product movement records (audit trail cleanup)
     * 3. Deletes return product line items
     * 4. Deletes the return record
     * 
     * Note: Stock restoration uses the same unit conversion logic
     * to ensure accurate inventory levels.
     * 
     * @param GoodsReceivedNoteReturn $grnReturn - The return to delete (route model binding)
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(GoodsReceivedNoteReturn $grnReturn)
    {
        // Start database transaction for atomicity
        DB::beginTransaction();
        try {
            // Get return products before deletion for stock restoration
            $returnProducts = GoodsReceivedNoteReturnProduct::where('goods_received_note_return_id', $grnReturn->id)->get();
            
            // Track total return amount for GRN subtotal restoration
            $totalReturnAmount = 0;
            
            // Restore stock for related products and remove related product movements
            $existing = GoodsReceivedNoteReturnProduct::where('goods_received_note_return_id', $grnReturn->id)->get();
            // Restore stock for each returned product
            foreach ($existing as $ex) {
                // Restore the quantity in the original GRN product record
                $grnProduct = GoodsReceivedNoteProduct::where('goods_received_note_id', $grnReturn->goods_received_note_id)
                    ->where('product_id', $ex->product_id)
                    ->first();
                
                if ($grnProduct) {
                    $grnProduct->increment('quantity', $ex->quantity);
                    
                    $returnedQty = is_numeric($ex->quantity) ? (float)$ex->quantity : floatval($ex->quantity);
                    $selectedUnitId = (int)$ex->measurement_unit_id;
                    
                    // Get product with fresh data from DB
                    $prod = Product::find($ex->product_id);
                    if ($prod) {
                        // Get purchase price from GRN product
                        $purchasePrice = (float)($grnProduct->purchase_price ?? 0);
                        
                        $purchaseUnitId = (int)$prod->purchase_unit_id;
                        $transferUnitId = (int)$prod->transfer_unit_id;
                        $salesUnitId = (int)$prod->sales_unit_id;
                        
                        $purchaseToTransferRate = (float)$prod->purchase_to_transfer_rate ?: 1.0;
                        $transferToSalesRate = (float)$prod->transfer_to_sales_rate ?: 1.0;
                        
                        // Calculate return amount based on unit type
                        $returnAmount = 0;
                        if ($selectedUnitId == $purchaseUnitId) {
                            // Returned in purchase units
                            $returnAmount = $returnedQty * $purchasePrice;
                        } elseif ($selectedUnitId == $transferUnitId) {
                            // Returned in transfer units - convert to purchase unit price
                            $transferPrice = $purchasePrice / $purchaseToTransferRate;
                            $returnAmount = $returnedQty * $transferPrice;
                        } elseif ($selectedUnitId == $salesUnitId) {
                            // Returned in sales units - convert to purchase unit price
                            $salesPrice = $purchasePrice / ($purchaseToTransferRate * $transferToSalesRate);
                            $returnAmount = $returnedQty * $salesPrice;
                        }
                        
                        // Add to total return amount
                        $totalReturnAmount += $returnAmount;
                        
                        // Restore the total in goods_received_note_products
                        $grnProduct->increment('total', $returnAmount);
                        
                        // Read raw database values directly
                        $dbRecord = DB::table('products')->where('id', $ex->product_id)->first();
                        $currentPurchaseQty = (float)($dbRecord->store_quantity_in_purchase_unit ?? 0);
                        $currentLooseQty = (float)($dbRecord->store_quantity_in_transfer_unit ?? 0);
                        
                        if ($selectedUnitId == $purchaseUnitId) {
                            // Restored in purchase units - increment directly
                            DB::table('products')
                                ->where('id', $ex->product_id)
                                ->increment('store_quantity_in_purchase_unit', $returnedQty);
                        } elseif ($selectedUnitId == $transferUnitId) {
                            // Restored in transfer units - convert and redistribute
                            
                            // Calculate total transfer units
                            $totalTransferUnits = ($currentPurchaseQty * $purchaseToTransferRate) + $currentLooseQty;
                            
                            // Add returned quantity back
                            $newTotalTransferUnits = $totalTransferUnits + $returnedQty;
                            
                            // Re-distribute into purchase units and loose bundles
                            $newPurchaseQty = floor($newTotalTransferUnits / $purchaseToTransferRate);
                            $newLooseQty = $newTotalTransferUnits % $purchaseToTransferRate;
                            
                            // Update using raw DB queries
                            $purchaseDifference = $newPurchaseQty - $currentPurchaseQty;
                            $looseDifference = $newLooseQty - $currentLooseQty;
                            
                            if ($purchaseDifference != 0) {
                                if ($purchaseDifference > 0) {
                                    DB::table('products')
                                        ->where('id', $ex->product_id)
                                        ->increment('store_quantity_in_purchase_unit', $purchaseDifference);
                                } else {
                                    DB::table('products')
                                        ->where('id', $ex->product_id)
                                        ->decrement('store_quantity_in_purchase_unit', abs($purchaseDifference));
                                }
                            }
                            
                            if ($looseDifference != 0) {
                                if ($looseDifference > 0) {
                                    DB::table('products')
                                        ->where('id', $ex->product_id)
                                        ->increment('store_quantity_in_transfer_unit', $looseDifference);
                                } else {
                                    DB::table('products')
                                        ->where('id', $ex->product_id)
                                        ->decrement('store_quantity_in_transfer_unit', abs($looseDifference));
                                }
                            }
                        } elseif ($selectedUnitId == $salesUnitId) {
                            // Restored in sales units - convert to transfer units first, then redistribute
                            $returnedInTransferUnits = $returnedQty / $transferToSalesRate;
                            
                            // Use the already-read values from above
                            // Calculate total transfer units
                            $totalTransferUnits = ($currentPurchaseQty * $purchaseToTransferRate) + $currentLooseQty;
                            
                            // Add returned quantity back (converted to transfer units)
                            $newTotalTransferUnits = $totalTransferUnits + $returnedInTransferUnits;
                            
                            // Re-distribute into purchase units and loose bundles
                            $newPurchaseQty = floor($newTotalTransferUnits / $purchaseToTransferRate);
                            $newLooseQty = $newTotalTransferUnits % $purchaseToTransferRate;
                            
                            // Update using raw DB queries
                            $purchaseDifference = $newPurchaseQty - $currentPurchaseQty;
                            $looseDifference = $newLooseQty - $currentLooseQty;
                            
                            if ($purchaseDifference != 0) {
                                if ($purchaseDifference > 0) {
                                    DB::table('products')
                                        ->where('id', $ex->product_id)
                                        ->increment('store_quantity_in_purchase_unit', $purchaseDifference);
                                } else {
                                    DB::table('products')
                                        ->where('id', $ex->product_id)
                                        ->decrement('store_quantity_in_purchase_unit', abs($purchaseDifference));
                                }
                            }
                            
                            if ($looseDifference != 0) {
                                if ($looseDifference > 0) {
                                    DB::table('products')
                                        ->where('id', $ex->product_id)
                                        ->increment('store_quantity_in_transfer_unit', $looseDifference);
                                } else {
                                    DB::table('products')
                                        ->where('id', $ex->product_id)
                                        ->decrement('store_quantity_in_transfer_unit', abs($looseDifference));
                                }
                            }
                        }
                    }
                }
            }

            // Delete previous product movement records tied to this GRN return (by reference)
            // This cleans up the audit trail for this return
            ProductMovement::where('reference', 'GRN Return #' . $grnReturn->id)->delete();

            // Restore GRN subtotal by adding back the total return amount
            if ($totalReturnAmount > 0) {
                GoodsReceivedNote::where('id', $grnReturn->goods_received_note_id)
                    ->increment('subtotal', $totalReturnAmount);
            }

            // Delete related product line items
            GoodsReceivedNoteReturnProduct::where('goods_received_note_return_id', $grnReturn->id)->delete();

            // Delete the main return record
            $grnReturn->delete();

            // Commit transaction - all operations succeeded
            DB::commit();
            return redirect()->route('grn-returns.index')->with('success', 'GRN return deleted.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed deleting GRN return: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
