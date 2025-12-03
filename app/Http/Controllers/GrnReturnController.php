<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GrnReturn;
use App\Models\GrnReturnProduct;
use App\Models\Grn;
use App\Models\Product;
use App\Models\ProductMovement;
use App\Models\MeasurementUnit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class GrnReturnController extends Controller
{
    public function index()
    {
        // eager-load GRN and its products so the view can reference original GRN quantities
        $returns = GrnReturn::with(['user', 'grn.grnProducts.product', 'grn_return_products.product'])->latest()->paginate(20);
        // eager-load GRN products so frontend can autofill on selection
        // serialize to plain array to avoid V8/proxy serialization differences in Inertia
        $grns = Grn::with(['grnProducts.product'])->orderByDesc('id')->get()->toArray();
        $user = auth()->user();
        // load available products and measurement units for the frontend
        $availableProducts = Product::where('status', '!=', 0)->orderBy('name')->get();
        // ensure measurement units are serialized as a plain array for Inertia
        $measurementUnits = MeasurementUnit::orderBy('name')->get()->toArray();

        return Inertia::render('GrnReturns/Index', compact('returns', 'grns', 'user', 'availableProducts', 'measurementUnits'));
    }

    public function create()
    {
        // include grnProducts so frontend can autofill products without extra routes
        // serialize to plain array for predictable client-side shape
        $grns = Grn::with(['grnProducts.product'])->orderByDesc('id')->get()->toArray();
        $products = Product::orderBy('name')->get();
        $measurementUnits = MeasurementUnit::orderBy('name')->get()->toArray();
        $user = auth()->user();
        return Inertia::render('GrnReturns/Create',[ 
        'grns' => $grns,
        'products' => $products,
        'measurementUnits' => $measurementUnits,
        'user' => $user,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'grn_id' => 'required|exists:grns,id',
            'date' => 'required|date',
            'user_id' => 'required|exists:users,id',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.qty' => 'required|numeric|min:1',
            'products.*.remarks' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $grnReturn = GrnReturn::create([
                'grn_id' => $validated['grn_id'],
                'date' => $validated['date'],
                'user_id' => $validated['user_id'],
            ]);

            foreach ($validated['products'] as $p) {
                GrnReturnProduct::create([
                    'grn_return_id' => $grnReturn->id,
                    // DB column is `products_id` (plural) per migration/model
                    'products_id' => $p['product_id'],
                    'qty' => $p['qty'],
                    'remarks' => $p['remarks'] ?? null,
                ]);
                // record product movement for BRN return (type 5)
                if (!empty($p['qty']) && $p['qty'] > 0) {
                    ProductMovement::record($p['product_id'], ProductMovement::TYPE_GRN_RETURN, $p['qty'], 'GRN Return #' . $grnReturn->id);
                    // decrement storage stock quantity
                    $prod = Product::find($p['product_id']);
                    if ($prod) {
                        // ensure numeric
                        $qty = is_numeric($p['qty']) ? (float)$p['qty'] : floatval($p['qty']);
                        // convert purchase unit -> transfer -> sales unit
                        $purchaseToTransfer = is_numeric($prod->purchase_to_transfer_rate) && $prod->purchase_to_transfer_rate > 0 ? (float)$prod->purchase_to_transfer_rate : 1.0;
                        $transferToSales = is_numeric($prod->transfer_to_sales_rate) && $prod->transfer_to_sales_rate > 0 ? (float)$prod->transfer_to_sales_rate : 1.0;
                        $converted = $qty * $purchaseToTransfer * $transferToSales;
                        // round to 4 decimal places to avoid tiny fractions
                        $converted = round($converted, 4);
                        $prod->increment('storage_stock_qty', $converted);
                    }
                }
            }

            DB::commit();
            return redirect()->route('grn-returns.index')->with('success', 'GRN return recorded.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function update(Request $request, GrnReturn $grnReturn)
    {
        $validated = $request->validate([
            'grn_id' => 'required|exists:grns,id',
            'date' => 'required|date',
            'user_id' => 'required|exists:users,id',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.qty' => 'required|numeric|min:0',
            'products.*.remarks' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // update main return
            $grnReturn->update([
                'grn_id' => $validated['grn_id'],
                'date' => $validated['date'],
                'user_id' => $validated['user_id'],
            ]);

            // restore stock and remove previous product movements for this return
            $existing = GrnReturnProduct::where('grn_return_id', $grnReturn->id)->get();
            foreach ($existing as $ex) {
                // add back previously subtracted qty (convert to sale unit)
                $prod = Product::find($ex->products_id);
                if ($prod) {
                    $qty = is_numeric($ex->qty) ? (float)$ex->qty : floatval($ex->qty);
                    $purchaseToTransfer = is_numeric($prod->purchase_to_transfer_rate) && $prod->purchase_to_transfer_rate > 0 ? (float)$prod->purchase_to_transfer_rate : 1.0;
                    $transferToSales = is_numeric($prod->transfer_to_sales_rate) && $prod->transfer_to_sales_rate > 0 ? (float)$prod->transfer_to_sales_rate : 1.0;
                    $converted = round($qty * $purchaseToTransfer * $transferToSales, 4);
                    $prod->increment('storage_stock_qty', $converted);
                }
            }
            // delete previous product movement records tied to this GRN return (by reference)
            ProductMovement::where('reference', 'GRN Return #' . $grnReturn->id)->delete();

            // remove existing product rows and recreate
            GrnReturnProduct::where('grn_return_id', $grnReturn->id)->delete();

            foreach ($validated['products'] as $p) {
                GrnReturnProduct::create([
                    'grn_return_id' => $grnReturn->id,
                    'products_id' => $p['product_id'],
                    'qty' => $p['qty'],
                    'remarks' => $p['remarks'] ?? null,
                ]);
                // record product movement for BRN return (type 5) and decrement stock
                if (!empty($p['qty']) && $p['qty'] > 0) {
                    ProductMovement::record($p['product_id'], ProductMovement::TYPE_GRN_RETURN, $p['qty'], 'GRN Return #' . $grnReturn->id);
                    $prod = Product::find($p['product_id']);
                    if ($prod) {
                        $qty = is_numeric($p['qty']) ? (float)$p['qty'] : floatval($p['qty']);
                        $purchaseToTransfer = is_numeric($prod->purchase_to_transfer_rate) && $prod->purchase_to_transfer_rate > 0 ? (float)$prod->purchase_to_transfer_rate : 1.0;
                        $transferToSales = is_numeric($prod->transfer_to_sales_rate) && $prod->transfer_to_sales_rate > 0 ? (float)$prod->transfer_to_sales_rate : 1.0;
                        $converted = round($qty * $purchaseToTransfer * $transferToSales, 4);
                        $prod->decrement('storage_stock_qty', $converted);
                    }
                }
            }

            DB::commit();
            return redirect()->route('grn-returns.index')->with('success', 'GRN return updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed updating GRN return: ' . $e->getMessage());
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy(GrnReturn $grnReturn)
    {
        DB::beginTransaction();
        try {
            // restore stock for related products and remove related product movements
            $existing = GrnReturnProduct::where('grn_return_id', $grnReturn->id)->get();
            foreach ($existing as $ex) {
                $prod = Product::find($ex->products_id);
                if ($prod) {
                    $qty = is_numeric($ex->qty) ? (float)$ex->qty : floatval($ex->qty);
                    $purchaseToTransfer = is_numeric($prod->purchase_to_transfer_rate) && $prod->purchase_to_transfer_rate > 0 ? (float)$prod->purchase_to_transfer_rate : 1.0;
                    $transferToSales = is_numeric($prod->transfer_to_sales_rate) && $prod->transfer_to_sales_rate > 0 ? (float)$prod->transfer_to_sales_rate : 1.0;
                    $converted = round($qty * $purchaseToTransfer * $transferToSales, 4);
                    $prod->increment('storage_stock_qty', $converted);
                }
            }

            // delete previous product movement records tied to this GRN return (by reference)
            ProductMovement::where('reference', 'GRN Return #' . $grnReturn->id)->delete();

            // delete related products
            GrnReturnProduct::where('grn_return_id', $grnReturn->id)->delete();

            // delete the return
            $grnReturn->delete();

            DB::commit();
            return redirect()->route('grn-returns.index')->with('success', 'GRN return deleted.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed deleting GRN return: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
