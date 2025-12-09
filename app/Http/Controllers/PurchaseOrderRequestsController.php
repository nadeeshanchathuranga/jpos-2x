<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrderRequest;
use App\Models\PurchaseOrderRequestProduct;
use App\Models\MeasurementUnit;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PurchaseOrderRequestsController extends Controller
{

   
    /**
     * Display a listing of PORs
     */
    public function index()
    {
     
      $purchaseOrderRequests = PurchaseOrderRequest::with([
        'por_products.product.purchaseUnit',
         
        'user'
    ])
        ->latest()
        ->paginate(10);
       
      
        // Load only low-stock products (store_quantity below store_low_stock_margin)
        $products = Product::where('status', '!=', 0)
            ->where(function ($query) {
                $query->whereNotNull('store_low_stock_margin')
                    ->whereColumn('store_quantity', '<', 'store_low_stock_margin');
            })
            ->with(['measurement_unit', 'purchaseUnit'])
            ->get();
        
        $measurementUnits = MeasurementUnit::orderBy('name')
            ->get();

        $users = User::orderBy('name')->get();

        $orderNumber = $this->generateOrderNumber();

        return Inertia::render('PurchaseOrderRequests/Index', [
            'purchaseOrderRequests' => $purchaseOrderRequests,
            'products' => $products,
            'measurementUnits' => $measurementUnits,
            'users' => $users,
            'orderNumber' => $orderNumber
        ]);
    }

    /**
     * Show the form for creating a new POR
     */
  
    /**
     * Store a newly created POR
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_number' => 'required|string|unique:purchase_order_requests,order_number',
            'order_date' => 'required|date',
            'user_id' => 'required|exists:users,id',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.requested_quantity' => 'required|integer|min:1',
            'products.*.measurement_unit_id' => 'required|exists:measurement_units,id'
        ]);

        DB::beginTransaction();
        
        try {
            $purchaseOrderRequest = PurchaseOrderRequest::create([
                'order_number' => $validated['order_number'],
                'order_date' => $validated['order_date'],
                'user_id' => $validated['user_id'],
                'total_amount' => 0,
                'status' => 'pending',
                'created_by' => Auth::id()
            ]);

            foreach ($validated['products'] as $productData) {
                PurchaseOrderRequestProduct::create([
                    'purchase_order_request_id' => $purchaseOrderRequest->id,
                    'product_id' => $productData['product_id'],
                    'requested_quantity' => $productData['requested_quantity'],
                    'measurement_unit_id' => $productData['measurement_unit_id']
                ]);
            }

            DB::commit();

            return redirect()->route('purchase-order-requests.index')
                ->with('success', 'Purchase Order Request created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withErrors([
                'error' => 'Failed to create POR: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Display the specified POR
     */
    
    /**
     * Update the status of the specified POR
     */
    public function updateStatus(Request $request, PurchaseOrderRequest $purchaseOrderRequest)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected,completed'
        ]);
        
        $purchaseOrderRequest->update(['status' => $request->status]);
        
        return back()->with('success', 'Status updated successfully');
    }

    /**
     * Delete the specified POR
     */
    public function destroy(PurchaseOrderRequest $purchaseOrderRequest)
    {
        // Only allow deletion if status is pending
        if ($purchaseOrderRequest->status !== 'pending') {
            return back()->withErrors([
                'error' => 'Only pending PORs can be deleted. Current status: ' . ucfirst($purchaseOrderRequest->status)
            ]);
        }

        DB::beginTransaction();
        
        try {
            // Delete related products
            PurchaseOrderRequestProduct::where('purchase_order_request_id', $purchaseOrderRequest->id)->delete();
            
            // Delete the POR
            $purchaseOrderRequest->delete();
            
            DB::commit();
            
            return back()->with('success', 'Purchase Order Request deleted successfully');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withErrors([
                'error' => 'Failed to delete POR: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Update the specified POR
     */
    public function update(Request $request, PurchaseOrderRequest $purchaseOrderRequest)
    {
        // Only allow update if status is pending
        if ($purchaseOrderRequest->status !== 'pending') {
            return back()->withErrors([
                'error' => 'Only pending PORs can be updated. Current status: ' . ucfirst($purchaseOrderRequest->status)
            ]);
        }

        $validated = $request->validate([
            'order_date' => 'required|date',
            'user_id' => 'required|exists:users,id',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.requested_quantity' => 'required|integer|min:1',
            'products.*.measurement_unit_id' => 'required|exists:measurement_units,id'
        ]);

        DB::beginTransaction();
        
        try {
            // Update POR
            $purchaseOrderRequest->update([
                'order_date' => $validated['order_date'],
                'user_id' => $validated['user_id']
            ]);

            // Delete existing products
            PurchaseOrderRequestProduct::where('purchase_order_request_id', $purchaseOrderRequest->id)->delete();

            // Add new products
            foreach ($validated['products'] as $productData) {
                PurchaseOrderRequestProduct::create([
                    'purchase_order_request_id' => $purchaseOrderRequest->id,
                    'product_id' => $productData['product_id'],
                    'requested_quantity' => $productData['requested_quantity'],
                    'measurement_unit_id' => $productData['measurement_unit_id']
                ]);
            }

            DB::commit();

            return redirect()->route('purchase-order-requests.index')
                ->with('success', 'Purchase Order Request updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withErrors([
                'error' => 'Failed to update POR: ' . $e->getMessage()
            ]);
        }
    }

    private function generateOrderNumber()
    {
        $prefix = 'POR';
        $date = date('Ymd');
        $lastPor = PurchaseOrderRequest::whereDate('created_at', today())
            ->latest()
            ->first();
        
        $sequence = $lastPor ? (int)substr($lastPor->order_number, -4) + 1 : 1;
        
        return $prefix . '-' . $date . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }



 public function purchaseOrderDetails($id)
{
    try {
        // Load the Purchase Order
        $purchaseOrder = PurchaseOrderRequest::findOrFail($id);

        // Get products from por_products table, include measurement unit
        $purchaseOrderProducts = PurchaseOrderRequestProduct::where('purchase_order_request_id', $id)
    ->with(['product.purchaseUnit'])
    ->get()
    ->map(function($purchaseOrderProduct) {
        return [
            'product_id' => $purchaseOrderProduct->product_id,
            'name'       => $purchaseOrderProduct->product->name ?? 'N/A',
            'requested_quantity'   => $purchaseOrderProduct->requested_quantity ?? 1,
            'measurement_unit_id' => $purchaseOrderProduct->measurement_unit_id,
            'price'      => $purchaseOrderProduct->product->purchase_price ?? 0,
        ];
    });
 

        return response()->json([
            'purchaseOrder' => $purchaseOrder,
            'purchaseOrderProducts' => $purchaseOrderProducts
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Failed to load PO details',
            'message' => $e->getMessage()
        ], 404);
    }
}


}
