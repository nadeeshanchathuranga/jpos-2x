<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Grn;
use App\Models\GrnProduct;
use App\Models\Supplier;
use App\Models\Por;
use App\Models\Product;
use Inertia\Inertia;
use Illuminate\Http\Request;

class GrnController extends Controller
{
    public function index()
    {
        $grns = Grn::with(['grnProducts.product', 'supplier'])->paginate(10);
        $suppliers = Supplier::where('status', '!=', 0)->get();
        $purchaseOrders = Por::where('status', 'approved')->get();
        $products = Product::where('status', '!=', 0)->get();
        
        return Inertia::render('Grn/Index', [
            'grns' => $grns,
            'suppliers' => $suppliers,
            'purchaseOrders' => $purchaseOrders,
            'availableProducts' => $products,
             'grnNumber' => $this->generateGrnNumber(),
        ]);
    }

 

    public function store(Request $request)
    {
        $validated = $request->validate([
          'grn_no'   => $this->generateGrnNumber(),
            'supplier_id'   => 'required|exists:suppliers,id',
            'grn_date'      => 'required|date',
            'por_id'        => 'nullable|exists:pors,id',
            'discount'      => 'nullable|numeric|min:0',
            'tax_total'     => 'nullable|numeric|min:0',
            'remarks'       => 'nullable|string',

            'products'                      => 'required|array|min:1',
            'products.*.product_id'         => 'required|exists:products,id',
            'products.*.qty'                => 'required|numeric|min:0.01',
            'products.*.purchase_price'     => 'required|numeric|min:0',
     
            'products.*.discount'           => 'nullable|numeric|min:0',
        ]);


        DB::beginTransaction();

        try {
            $grn = Grn::create([
                'por_id'        => $validated['por_id'] ?? null,
                'grn_no'        => $validated['grn_no'],
                'supplier_id'   => $validated['supplier_id'],
                'user_id'       => auth()->id(),
                'grn_date'      => $validated['grn_date'],
                'discount'      => $validated['discount'] ?? 0,
                'tax_total'     => $validated['tax_total'] ?? 0,
                'remarks'       => $validated['remarks'] ?? null,
                'status'        => 1,
            ]);

            foreach ($validated['products'] as $product) {
                $lineTotal = ($product['qty'] * $product['purchase_price']) - ($product['discount'] ?? 0);

                GrnProduct::create([
                    'grn_id'         => $grn->id,
                    'product_id'     => $product['product_id'],
                    'qty'            => $product['qty'],
                    'purchase_price' => $product['purchase_price'],
                  
                    'discount'       => $product['discount'] ?? 0,
                    'total'          => $lineTotal,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('grn.index')
                ->with('success', 'GRN created successfully!');

        } catch (\Throwable $e) {
          
            DB::rollBack();

            return redirect()
                ->back()
                ->withErrors(['error' => 'Failed to create GRN: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function edit(Grn $grn)
    {
        $grn->load('grnProducts');
        $suppliers = Supplier::where('status', '!=', 0)->get();
        $purchaseOrders = Por::where('status', 'approved')->get();
        $products = Product::where('status', '!=', 0)->get();

        return Inertia::render('Grn/Edit', [
            'grn' => $grn,
            'suppliers' => $suppliers,
            'purchaseOrders' => $purchaseOrders,
            'availableProducts' => $products,
        ]);
    }

    public function update(Request $request, Grn $grn)
    {
        $validated = $request->validate([
            'grn_no'        => 'required|unique:grns,grn_no,' . $grn->id,
            'supplier_id'   => 'required|exists:suppliers,id',
            'grn_date'      => 'required|date',
            'por_id'        => 'nullable|exists:pors,id',
            'discount'      => 'nullable|numeric|min:0',
            'tax_total'     => 'nullable|numeric|min:0',
            'remarks'       => 'nullable|string',
            'status'        => 'required|in:0,1,2',
        ]);

        DB::beginTransaction();

        try {
            $grn->update([
                'grn_no'        => $validated['grn_no'],
                'supplier_id'   => $validated['supplier_id'],
                'grn_date'      => $validated['grn_date'],
                'por_id'        => $validated['por_id'] ?? null,
                'discount'      => $validated['discount'] ?? 0,
                'tax_total'     => $validated['tax_total'] ?? 0,
                'remarks'       => $validated['remarks'] ?? null,
                'status'        => $validated['status'],
            ]);

            DB::commit();

            return redirect()->route('grn.index')->with('success', 'GRN updated successfully');

        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withErrors(['error' => 'Failed to update GRN: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function updateStatus(Request $request, Grn $grn)
    {
        $validated = $request->validate([
            'status' => 'required|in:0,1,2',
        ]);

        $grn->update(['status' => $validated['status']]);

        return redirect()->back()->with('success', 'Status updated successfully');
    }

    public function destroy(Grn $grn)
    {
        $grn->update(['status' => 0]);

        return redirect()->back()->with('success', 'GRN marked as inactive successfully');
    }

    
    private function generateGrnNumber()
    {
        $prefix = 'GRN';
        $date = date('Ymd');

        // Find last GRN created today
        $lastGrn = Grn::whereDate('created_at', today())
            ->latest()
            ->first();

        // Extract last sequence
        $sequence = $lastGrn
            ? (int) substr($lastGrn->grn_no, -4) + 1   // <- use correct column name
            : 1;

        // Return final GRN number
        return $prefix . '-' . $date . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }




}
