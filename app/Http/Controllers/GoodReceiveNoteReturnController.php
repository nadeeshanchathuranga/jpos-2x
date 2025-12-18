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
        ->with('measurement_unit')
        ->orderBy('name')
        ->get();
    
    // Get all measurement units
    $measurementUnits = MeasurementUnit::orderBy('name')->get()->toArray();
  

    return Inertia::render('GoodsReceivedNoteReturns/Index', [
        'returns' => $returns,
        'goodsReceivedNotes' => $goodsReceivedNotes,
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
        $goodsReceivedNotes = GoodsReceivedNote::with(['goods_received_note_products.product'])
            ->where('status', '!=', 0)
            ->orderByDesc('id')
            ->get()
            ->toArray();
        
        // Load all products for manual product selection
        $products = Product::orderBy('name')->get();
        
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

            // Process each returned product
            foreach ($validated['products'] as $p) {
                // Create return product line item
                GoodsReceivedNoteReturnProduct::create([
                    'goods_received_note_return_id' => $grnReturn->id,
                    'product_id' => $p['product_id'],
                    'quantity' => $p['qty'],
                    'remarks' => $p['remarks'] ?? null,
                ]);
                
                // Record product movement for GRN return (Type 5: TYPE_GRN_RETURN)
                // This creates an audit trail for inventory tracking
                if (!empty($p['qty']) && $p['qty'] > 0) {
                    ProductMovement::record($p['product_id'], ProductMovement::TYPE_GRN_RETURN, $p['qty'], 'GRN Return #' . $grnReturn->id);
                    
                    // Increment store quantity (returned goods go back to store)
                    $prod = Product::find($p['product_id']);
                    if ($prod) {
                        // Convert quantity to float for calculation
                        $qty = is_numeric($p['qty']) ? (float)$p['qty'] : floatval($p['qty']);
                        
                        // Get conversion rates (default to 1.0 if not set)
                        $purchaseToTransfer = is_numeric($prod->purchase_to_transfer_rate) && $prod->purchase_to_transfer_rate > 0 ? (float)$prod->purchase_to_transfer_rate : 1.0;
                        $transferToSales = is_numeric($prod->transfer_to_sales_rate) && $prod->transfer_to_sales_rate > 0 ? (float)$prod->transfer_to_sales_rate : 1.0;
                        
                        // Apply conversion: purchase unit → transfer unit → sales unit
                        // Example: 1 carton (purchase) → 12 packs (transfer) → 144 pieces (sales)
                        $converted = round($qty * $purchaseToTransfer * $transferToSales, 4);
                        
                        // Increment store quantity by converted amount
                        $prod->increment('store_quantity', $converted);
                    }
                }
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
            // Restore stock for related products and remove related product movements
            $existing = GoodsReceivedNoteReturnProduct::where('goods_received_note_return_id', $grnReturn->id)->get();
            // Restore stock for each returned product
            foreach ($existing as $ex) {
                $prod = Product::find($ex->products_id);
                if ($prod) {
                    // Convert quantity to float
                    $qty = is_numeric($ex->qty) ? (float)$ex->qty : floatval($ex->qty);
                    
                    // Get conversion rates (default to 1.0)
                    $purchaseToTransfer = is_numeric($prod->purchase_to_transfer_rate) && $prod->purchase_to_transfer_rate > 0 ? (float)$prod->purchase_to_transfer_rate : 1.0;
                    $transferToSales = is_numeric($prod->transfer_to_sales_rate) && $prod->transfer_to_sales_rate > 0 ? (float)$prod->transfer_to_sales_rate : 1.0;
                    
                    // Apply same conversion to ensure accurate restoration
                    $converted = round($qty * $purchaseToTransfer * $transferToSales, 4);
                    
                    // Increment store quantity (restore the returned stock)
                    $prod->increment('store_quantity', $converted);
                }
            }

            // Delete previous product movement records tied to this GRN return (by reference)
            // This cleans up the audit trail for this return
            ProductMovement::where('reference', 'GRN Return #' . $grnReturn->id)->delete();

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
