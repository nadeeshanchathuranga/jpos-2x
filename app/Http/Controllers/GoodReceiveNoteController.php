<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\GoodsReceivedNote;
use App\Models\GoodsReceivedNoteProduct;
use App\Models\Supplier;
use App\Models\PurchaseOrderRequest;
use App\Models\Product;
use App\Models\ProductMovement;
use App\Models\MeasurementUnit;
use Inertia\Inertia;
use Illuminate\Http\Request;

class GoodReceiveNoteController extends Controller
{
    public function index()
    {
$grns = GoodsReceivedNote::with(['grnProducts.product', 'grnProducts.product.measurement_unit', 'supplier'])
           ->paginate(10);
        $suppliers = Supplier::where('status', '!=', 0)->get();
         $purchaseOrders = PurchaseOrderRequest::all();
        $products = Product::where('status', '!=', 0)->get();
   $measurementUnits = MeasurementUnit::orderBy('name')
            ->get();
        return Inertia::render('GoodReceiveNote/Index', [
            'goodReceiveNotes' => $grns,
            'measurementUnits' => $measurementUnits,
            'suppliers' => $suppliers,
            'purchaseOrders' => $purchaseOrders,
            'availableProducts' => $products,
             'goodReceiveNoteNumber' => $this->generateGoodReceiveNoteNumber(),
        ]);
    }

 

    public function store(Request $request)
    {
        $validated = $request->validate([
            'good_received_note_no'   => 'required|string|unique:grns,grn_no',
            'supplier_id'   => 'required|exists:suppliers,id',
            'good_received_note_date'      => 'required|date',
            'purchase_order_request_id'        => 'nullable|exists:purchase_order_requests,id',
            'discount'      => 'nullable|numeric|min:0',
            'tax_total'     => 'nullable|numeric|min:0',
            'remarks'       => 'nullable|string',

            'products'                      => 'required|array|min:1',
            'products.*.product_id'         => 'required|exists:products,id',
            'products.*.quantity'                => 'required|numeric|min:0.01',
            'products.*.purchase_price'     => 'required|numeric|min:0',
            'products.*.discount'           => 'nullable|numeric|min:0',
            'products.*.unit'               => 'nullable|string',
            'products.*.total'              => 'nullable|numeric',
        ]);

        DB::beginTransaction();

        try {
            $grn = GoodsReceivedNote::create([
                'por_id'        => $validated['por_id'] ?? null,
                'grn_no'        => $validated['grn_no'],
                'supplier_id'   => $validated['supplier_id'],
                'user_id'       => auth()->id(),
                'good_received_note_date'      => $validated['good_received_note_date'],
                'discount'      => $validated['discount'] ?? 0,
                'tax_total'     => $validated['tax_total'] ?? 0,
                'remarks'       => $validated['remarks'] ?? null,
                'status'        => 1,
            ]);

            foreach ($validated['products'] as $product) {
                $lineTotal = ($product['quantity'] * $product['purchase_price']) - ($product['discount'] ?? 0);

                GoodReceiveNoteProduct::create([
                    'good_receive_note_id'         => $grn->id,
                    'product_id'     => $product['product_id'],
                    'quantity'            => $product['quantity'],
                    'purchase_price' => $product['purchase_price'],
                    'discount'       => $product['discount'] ?? 0,
                    'total'          => $lineTotal,
                ]);

                // Record product movement (Purchase - increases stock)
                ProductMovement::recordMovement(
                    $product['product_id'],
                    ProductMovement::TYPE_PURCHASE,
                    $product['quantity'], // Positive for stock increase
                    $validated['good_received_note_no']
                );

                // Increment storage stock quantity on the product
                Product::where('id', $product['product_id'])
                    ->increment('quantity', (int) $product['quantity']);
            }

            DB::commit();

            return redirect()
                ->route('good-receive-notes.index')
                ->with('success', 'GRN created successfully!');

        } catch (\Throwable $e) {
        
            DB::rollBack();

            return redirect()
                ->back()
                ->withErrors(['error' => 'Failed to create Good Receive Note: ' . $e->getMessage()])
                ->withInput();
        }
    }

    // public function edit(Grn $grn)
    // {
    //     $grn->load('grnProducts');
    //     $suppliers = Supplier::where('status', '!=', 0)->get();
    //     $purchaseOrders = Por::where('status', 'approved')->get();
    //     $products = Product::where('status', '!=', 0)->get();

    //     return Inertia::render('Grn/Edit', [
    //         'grn' => $grn,
    //         'suppliers' => $suppliers,
    //         'purchaseOrders' => $purchaseOrders,
    //         'availableProducts' => $products,
    //     ]);
    // }

    public function update(Request $request, Grn $grn)
    {
        $validated = $request->validate([
            'good_received_note_no'        => 'required|unique:good_receive_notes,good_received_note_no,' . $grn->id,
            'supplier_id'   => 'required|exists:suppliers,id',
            'good_received_note_date'      => 'required|date',
            'purchase_order_request_id'        => 'nullable|exists:purchase_order_requests,id',
            'discount'      => 'nullable|numeric|min:0',
            'tax_total'     => 'nullable|numeric|min:0',
            'remarks'       => 'nullable|string',
            'status'        => 'required|in:0,1,2',
        ]);

        DB::beginTransaction();

        try {
            $grn->update([
                'good_received_note_no'        => $validated['good_received_note_no'],
                'supplier_id'   => $validated['supplier_id'],
                'good_received_note_date'      => $validated['good_received_note_date'],
                'purchase_order_request_id'        => $validated['purchase_order_request_id'] ?? null,
                'discount'      => $validated['discount'] ?? 0,
                'tax_total'     => $validated['tax_total'] ?? 0,
                'remarks'       => $validated['remarks'] ?? null,
                'status'        => $validated['status'],
            ]);

            DB::commit();

            return redirect()->route('good-receive-notes.index')->with('success', 'Good Receive Note updated successfully');

        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withErrors(['error' => 'Failed to update Good Receive Note: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function updateStatus(Request $request, GoodReceiveNote $goodReceiveNote)
    {
        $validated = $request->validate([
            'status' => 'required|in:0,1,2',
        ]);

        $goodReceiveNote->update(['status' => $validated['status']]);

        return redirect()->back()->with('success', 'Status updated successfully');
    }

    public function destroy(GoodReceiveNote $goodReceiveNote)
    {
        $goodReceiveNote->update(['status' => 0]);

        return redirect()->back()->with('success', 'Good Receive Note marked as inactive successfully');
    }

    
    private function generateGoodReceiveNoteNumber()
    {
        $prefix = 'GoodReceiveNote';
        $date = date('Ymd');

        // Find last GRN created today
        $lastGrn = GoodsReceivedNote::whereDate('created_at', today())
            ->latest()
            ->first();

        // Extract last sequence
        $sequence = $lastGrn
            ? (int) substr($lastGrn->good_received_note_no, -4) + 1
            : 1;

        // Return final GRN number
        return $prefix . '-' . $date . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }




}
