<?php

namespace App\Http\Controllers;

use App\Models\Ptr;
use App\Models\PtrProduct;
use App\Models\Product;
use App\Models\MeasurementUnit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PtrController extends Controller
{
    public function index()
    {
        $ptrs = Ptr::with(['user', 'ptr_products.product', 'ptr_products.measurement_unit'])
            ->paginate(10);

           
        
        $products = Product::all();
        $measurementUnits = MeasurementUnit::all();
        $users = User::all();
        $transferNo = 'PTR-' . date('YmdHis');

        return Inertia::render('Ptr/Index', [
            'ptrs' => $ptrs,
            'products' => $products,
            'measurementUnits' => $measurementUnits,
            'users' => $users,
            'transfer_no' => $transferNo
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'transfer_no' => 'required|string|unique:ptrs,transfer_no',
            'request_date' => 'required|date',
            'user_id' => 'required|exists:users,id',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.requested_qty' => 'required|integer|min:1',
            'products.*.unit_id' => 'nullable|exists:measurement_units,id'
        ]);

        DB::beginTransaction();
        
        try {
            $ptr = Ptr::create([
                'transfer_no' => $validated['transfer_no'],
                'request_date' => $validated['request_date'],
                'user_id' => $validated['user_id'],
                'status' => 'pending', 
            ]);

            foreach ($validated['products'] as $productData) {
                PtrProduct::create([
                    'ptr_id' => $ptr->id,
                    'product_id' => $productData['product_id'],
                    'requested_qty' => $productData['requested_qty'],
                    'unit_id' => $productData['unit_id'] ?? null
                ]);
            }

            DB::commit();

            return redirect()->route('ptr.index')
                ->with('success', 'Purchase Order Request created successfully');

        } catch (\Exception $e) {

            
            DB::rollBack();
            
            return back()->withErrors([
                'error' => 'Failed to create PTR: ' . $e->getMessage()
            ]);
        }
    }

  
    public function update(Request $request, Ptr $ptr)
    {
        if ($ptr->status !== 'pending') {
            return redirect()->back()->with('error', 'Only pending orders can be edited');
        }

        $validated = $request->validate([
            'transfer_no' => 'required|unique:ptrs,transfer_no,' . $ptr->id,
            'request_date' => 'required|date',
            'user_id' => 'required|exists:users,id',
            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.requested_qty' => 'required|numeric|min:1',
            'products.*.unit_id' => 'nullable|exists:measurement_units,id'
        ]);

        $ptr->update([
            'transfer_no' => $validated['transfer_no'],
            'request_date' => $validated['request_date'],
            'user_id' => $validated['user_id']
        ]);

        PtrProduct::where('ptr_id', $ptr->id)->delete();

        foreach ($validated['products'] as $product) {
            PtrProduct::create([
                'ptr_id' => $ptr->id,
                'product_id' => $product['product_id'],
                'requested_qty' => $product['requested_qty'],
                'unit_id' => $product['unit_id']
            ]);
        }

        return redirect()->route('ptr.index')->with('success', 'Purchase Order Request updated successfully');
    }

    public function destroy(Ptr $ptr)
    {
        if ($ptr->status !== 'pending') {
            return redirect()->back()->with('error', 'Only pending orders can be deleted');
        }

        $ptr->delete();
        return redirect()->route('ptr.index')->with('success', 'Purchase Order Request deleted successfully');
    }

    public function updateStatus(Request $request, Ptr $ptr)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected,completed'
        ]);
        
        $ptr->update(['status' => $request->status]);
        
        return back()->with('success', 'Status updated successfully');
    }


    public function ptrDetails($id)
{
    try {
        // Load the Product Transfer Request
        $ptr = Ptr::with(['ptr_products.product', 'user'])
            ->findOrFail($id);

        // Get products from ptr_products table
        $ptrProducts = PtrProduct::where('ptr_id', $id)
            ->with(['product'])
            ->get()
            ->map(function($ptrProduct) {
                return [
                    'product_id' => $ptrProduct->product_id,
                    'name'       => $ptrProduct->product->name ?? 'N/A',
                    'qty'        => $ptrProduct->requested_qty ?? 1,
                    'price'      => $ptrProduct->product->price ?? 0,
                    'unit'       => $ptrProduct->product->measurementUnit->name ?? 
                                   $ptrProduct->measurement_unit->name ?? 'N/A',
                ];
            });


            

        return response()->json([
            'ptr' => $ptr,
            'ptrProducts' => $ptrProducts
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Failed to load PTR details',
            'message' => $e->getMessage()
        ], 404);
    }
}
}
