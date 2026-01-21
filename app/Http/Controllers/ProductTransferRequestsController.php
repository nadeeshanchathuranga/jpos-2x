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

            // If the request is auto-approved (created by admin), perform immediate stock transfer
            if ($status === 'approved') {
                foreach ($validated['products'] as $productData) {
                    $product = Product::find($productData['product_id']);

                    if ($product) {
                        // Convert requested qty (transfer unit) to purchase + sales units for accurate stock moves
                        $purchaseToTransferRate = $product->purchase_to_transfer_rate ?? 1;
                        $transferToSalesRate = $product->transfer_to_sales_rate ?? 1;

                        $quantityInPurchaseUnits = $purchaseToTransferRate > 0
                            ? $productData['requested_quantity'] / $purchaseToTransferRate
                            : 0;

                        $quantityInSalesUnits = $productData['requested_quantity'] * $transferToSalesRate;

                        $available = $product->store_quantity_in_purchase_unit ?? 0;
                        $unitSymbol = $product->purchaseUnit?->symbol ?: '';

                        if ($available < $quantityInPurchaseUnits) {
                            DB::rollBack();
                            return back()->withErrors([
                                'error' => "Insufficient store quantity for product: {$product->name}. Available: {$available} {$unitSymbol}, Required: {$quantityInPurchaseUnits} {$unitSymbol}"
                            ]);
                        }

                        // Transfer stock: Store (purchase units) -> Shop (sales units)
                        $product->decrement('store_quantity_in_purchase_unit', $quantityInPurchaseUnits);
                        $product->increment('shop_quantity_in_sales_unit', $quantityInSalesUnits);
                    }
                }
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

            // If changing from pending to approved, transfer the stock
            if ($oldStatus === 'pending' && $newStatus === 'approved') {
                foreach ($productTransferRequest->product_transfer_request_products as $requestProduct) {
                    $product = Product::find($requestProduct->product_id);

                    if ($product) {
                        // Get conversion rates
                        $purchaseToTransferRate = $product->purchase_to_transfer_rate ?? 1;
                        $transferToSalesRate = $product->transfer_to_sales_rate ?? 1;
                        
                        // Convert requested quantity (in transfer units) to purchase units for store deduction
                        $quantityInPurchaseUnits = $purchaseToTransferRate > 0 
                            ? $requestProduct->requested_quantity / $purchaseToTransferRate 
                            : 0;
                        
                        // Convert requested quantity (in transfer units) to sales units for shop addition
                        $quantityInSalesUnits = $requestProduct->requested_quantity * $transferToSalesRate;
                        
                        // Check if store has enough quantity (in purchase units)
                        if ($product->store_quantity_in_purchase_unit < $quantityInPurchaseUnits) {
                            DB::rollBack();
                            $unitSymbol = $product->purchaseUnit ? $product->purchaseUnit->symbol : '';
                            return back()->withErrors([
                                'error' => "Insufficient store quantity for product: {$product->name}. Available: {$product->store_quantity_in_purchase_unit} {$unitSymbol}, Required: {$quantityInPurchaseUnits} {$unitSymbol}"
                            ]);
                        }
                        
                        // Transfer stock: Store (purchase units) -> Shop (sales units)
                        $product->decrement('store_quantity_in_purchase_unit', $quantityInPurchaseUnits);
                        $product->increment('shop_quantity_in_sales_unit', $quantityInSalesUnits);
                    }
                }
            }

            // If changing from approved back to pending/rejected, reverse the transfer
            if ($oldStatus === 'approved' && ($newStatus === 'pending' || $newStatus === 'rejected')) {
                foreach ($productTransferRequest->product_transfer_request_products as $requestProduct) {
                    $product = Product::find($requestProduct->product_id);

                    if ($product) {
                        // Get conversion rates
                        $purchaseToTransferRate = $product->purchase_to_transfer_rate ?? 1;
                        $transferToSalesRate = $product->transfer_to_sales_rate ?? 1;
                        
                        // Convert requested quantity (in transfer units) back to purchase units for store
                        $quantityInPurchaseUnits = $purchaseToTransferRate > 0 
                            ? $requestProduct->requested_quantity / $purchaseToTransferRate 
                            : 0;
                        
                        // Convert requested quantity (in transfer units) back to sales units for shop
                        $quantityInSalesUnits = $requestProduct->requested_quantity * $transferToSalesRate;
                        
                        // Reverse transfer: Shop (sales units) -> Store (purchase units)
                        $product->increment('store_quantity_in_purchase_unit', $quantityInPurchaseUnits);
                        $product->decrement('shop_quantity_in_sales_unit', $quantityInSalesUnits);
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
