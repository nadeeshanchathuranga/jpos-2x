<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\PrNote;
use App\Models\PrNoteProduct;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Ptr;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;

class PrnController extends Controller
{
    public function index()
    {
        $prns = PrNote::with(['prnProducts.product', 'supplier'])->paginate(10);
        $suppliers = Supplier::where('status', '!=', 0)->get();
        $products = Product::all();
        $ptrs = Ptr::all();
       
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
        $validated = $request->validate([
            'ptr_id'                        => 'required|exists:ptrs,id',
            'user_id'                       => 'nullable|exists:users,id',
            'release_date'                  => 'required|date',
            'status'                        => 'required|in:draft,pending,completed',
            'remark'                        => 'nullable|string',

            'products'                      => 'required|array|min:1',
            'products.*.product_id'         => 'required|exists:products,id',
            'products.*.qty'                => 'required|numeric|min:0.01',
            'products.*.purchase_price'     => 'required|numeric|min:0',
            'products.*.discount'           => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $prn = PrNote::create([
                'ptr_id'        => $validated['ptr_id'],
                'user_id'       => $validated['user_id'] ?? auth()->id(),
                'release_date'  => $validated['release_date'],
                'status'        => $validated['status'],
                'remark'        => $validated['remark'] ?? null,
            ]);

            foreach ($validated['products'] as $product) {
                $lineTotal = ($product['qty'] * $product['purchase_price']) - ($product['discount'] ?? 0);

                PrNoteProduct::create([
                    'prn_id'         => $prn->id,
                    'product_id'     => $product['product_id'],
                    'qty'            => $product['qty'],
                    'purchase_price' => $product['purchase_price'],
                    'discount'       => $product['discount'] ?? 0,
                    'total'          => $lineTotal,
                ]);
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
            'prn_no'        => 'required|unique:prns,prn_no,' . $prn->id,
            'supplier_id'   => 'required|exists:suppliers,id',
            'prn_date'      => 'required|date',
            'discount'      => 'nullable|numeric|min:0',
            'tax_total'     => 'nullable|numeric|min:0',
            'remarks'       => 'nullable|string',
            'status'        => 'required|in:0,1,2',
        ]);

        DB::beginTransaction();

        try {
            $prn->update([
                'prn_no'        => $validated['prn_no'],
                'supplier_id'   => $validated['supplier_id'],
                'prn_date'      => $validated['prn_date'],
                'discount'      => $validated['discount'] ?? 0,
                'tax_total'     => $validated['tax_total'] ?? 0,
                'remarks'       => $validated['remarks'] ?? null,
                'status'        => $validated['status'],
            ]);

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

    public function updateStatus(Request $request, PrNote $prn)
    {
        $validated = $request->validate([
            'status' => 'required|in:0,1,2',
        ]);

        $prn->update(['status' => $validated['status']]);

        return redirect()->back()->with('success', 'Status updated successfully');
    }

    public function destroy(PrNote $prn)
    {
        $prn->update(['status' => 0]);

        return redirect()->back()->with('success', 'PRN marked as inactive successfully');
    }
}
