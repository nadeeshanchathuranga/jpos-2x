<?php

namespace App\Http\Controllers;

use App\Models\ProductTransferRequest;
use App\Models\ProductTransferRequestProduct;
use App\Models\Product;
use App\Models\MeasurementUnit;
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

           
        
        $products = Product::all();
        $measurementUnits = MeasurementUnit::all();
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
            $productTransferRequest = ProductTransferRequest::create([
                'product_transfer_request_no' => $validated['product_transfer_request_no'],
                'request_date' => $validated['request_date'],
                'user_id' => $validated['user_id'],
                'status' => 'pending', 
            ]);

            foreach ($validated['products'] as $productData) {
                ProductTransferRequestProduct::create([
                    'product_transfer_request_id' => $productTransferRequest->id,
                    'product_id' => $productData['product_id'],
                    'requested_quantity' => $productData['requested_quantity'],
                    'unit_id' => $productData['unit_id'] ?? null
                ]);
            }

            DB::commit();

            return redirect()->route('product-transfer-requests.index')
                ->with('success', 'Purchase Order Request created successfully');

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
            'transfer_no' => 'required|unique:product_transfer_requests,transfer_no,' . $productTransferRequest->id,
            'request_date' => 'required|date',
            'user_id' => 'required|exists:users,id',
            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.requested_quantity' => 'required|numeric|min:1',
            'products.*.unit_id' => 'nullable|exists:measurement_units,id'
        ]);

        $ptr->update([
            'transfer_no' => $validated['transfer_no'],
            'request_date' => $validated['request_date'],
            'user_id' => $validated['user_id']
        ]);

        ProductTransferRequestProduct::where('product_transfer_request_id', $productTransferRequest->id)->delete();

        foreach ($validated['products'] as $product) {
            ProductTransferRequestProduct::create([
                'product_transfer_request_id' => $productTransferRequest->id,
                'product_id' => $product['product_id'],
                'requested_quantity' => $product['requested_quantity'],
                'unit_id' => $product['unit_id']
            ]);
        }

        return redirect()->route('product-transfer-requests.index')->with('success', 'Purchase Order Request updated successfully');
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
        
        $productTransferRequest->update(['status' => $request->status]);
        
        return back()->with('success', 'Status updated successfully');
    }


    public function productTransferRequestDetails($id)
{
    try {
        // Load the Product Transfer Request
        $productTransferRequest = ProductTransferRequest::with(['product_transfer_request_products.product', 'user'])
            ->findOrFail($id);

        // Get products from product_transfer_request_products table
        $productTransferRequestProducts = ProductTransferRequestProduct::where('product_transfer_request_id', $id)
            ->with(['product'])
            ->get()
            ->map(function($productTransferRequestProduct) {
                return [
                    'product_id' => $productTransferRequestProduct->product_id,
                    'name'       => $productTransferRequestProduct->product->name ?? 'N/A',
                    'qty'        => $productTransferRequestProduct->requested_qty ?? 1,
                    'price'      => $productTransferRequestProduct->product->price ?? 0,
                    'unit'       => $productTransferRequestProduct->product->measurementUnit->name ?? 
                                   $productTransferRequestProduct->measurement_unit->name ?? 'N/A',
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
