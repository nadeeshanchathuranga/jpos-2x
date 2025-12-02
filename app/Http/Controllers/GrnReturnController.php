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

            // remove existing product rows and recreate
            GrnReturnProduct::where('grn_return_id', $grnReturn->id)->delete();

            foreach ($validated['products'] as $p) {
                GrnReturnProduct::create([
                    'grn_return_id' => $grnReturn->id,
                    'products_id' => $p['product_id'],
                    'qty' => $p['qty'],
                    'remarks' => $p['remarks'] ?? null,
                ]);
                // record product movement for BRN return (type 5)
                if (!empty($p['qty']) && $p['qty'] > 0) {
                    ProductMovement::record($p['product_id'], ProductMovement::TYPE_GRN_RETURN, $p['qty'], 'GRN Return #' . $grnReturn->id);
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
            // delete related products first
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
