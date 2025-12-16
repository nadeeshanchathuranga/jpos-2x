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
        
        DB::beginTransaction();
        
        try {
            $oldStatus = $productTransferRequest->status;
            $newStatus = $request->status;
            
            // If changing from pending to approved, transfer the stock
            if ($oldStatus === 'pending' && $newStatus === 'approved') {
                foreach ($productTransferRequest->product_transfer_request_products as $requestProduct) {
                    $product = Product::find($requestProduct->product_id);
                    
                    if ($product) {
                        // Check if store has enough quantity
                        if ($product->store_quantity < $requestProduct->requested_quantity) {
                            DB::rollBack();
                            return back()->withErrors([
                                'error' => "Insufficient store quantity for product: {$product->name}. Available: {$product->store_quantity}, Required: {$requestProduct->requested_quantity}"
                            ]);
                        }
                        
                        // Transfer stock: Store -> Shop
                        $product->decrement('store_quantity', $requestProduct->requested_quantity);
                        $product->increment('shop_quantity', $requestProduct->requested_quantity);
                    }
                }
            }
            
            // If changing from approved back to pending/rejected, reverse the transfer
            if ($oldStatus === 'approved' && ($newStatus === 'pending' || $newStatus === 'rejected')) {
                foreach ($productTransferRequest->product_transfer_request_products as $requestProduct) {
                    $product = Product::find($requestProduct->product_id);
                    
                    if ($product) {
                        // Reverse transfer: Shop -> Store
                        $product->increment('store_quantity', $requestProduct->requested_quantity);
                        $product->decrement('shop_quantity', $requestProduct->requested_quantity);
                    }
                }
            }
            
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
            ->with(['product', 'measurement_unit', 'product.measurement_unit'])
            ->get()
            ->map(function($productTransferRequestProduct) {
                $product = $productTransferRequestProduct->product;
                $unitName = optional($product?->measurement_unit)->name
                    ?? optional($productTransferRequestProduct->measurement_unit)->name
                    ?? 'N/A';

                // Prefer a transfer price if available, otherwise use retail price, or fallback to 0
                $price = $product->transfer_price
                    ?? $product->retail_price
                    ?? 0;

                return [
                    'product_id' => $productTransferRequestProduct->product_id,
                    'name'       => $product->name ?? 'N/A',
                    'qty'        => $productTransferRequestProduct->requested_quantity ?? 0,
                    'price'      => (float) $price,
                    'unit'       => $unitName,
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
