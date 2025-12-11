<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\GoodsReceivedNote;
use App\Models\GoodsReceivedNoteProduct;
use App\Models\Supplier;
use App\Models\PurchaseOrderRequest;
use App\Models\PurchaseOrderRequestProduct;
use App\Models\Product;
use App\Models\ProductMovement;
use App\Models\MeasurementUnit;
use Inertia\Inertia;
use Illuminate\Http\Request;

class GoodReceiveNoteController extends Controller
{
    public function index()
    {
        $goodsReceivedNotes = GoodsReceivedNote::with(['goods_received_note_products.product', 'goods_received_note_products.product.measurement_unit', 'supplier'])
            ->paginate(10);
        $suppliers = Supplier::where('status', '!=', 0)->get();
        $purchaseOrders = PurchaseOrderRequest::all();
        $products = Product::where('status', '!=', 0)->get();
        $measurementUnits = MeasurementUnit::orderBy('name')
            ->get();
        return Inertia::render('GoodsReceivedNotes/Index', [
            'goodsReceivedNotes' => $goodsReceivedNotes,
            'measurementUnits' => $measurementUnits,
            'suppliers' => $suppliers,
            'purchaseOrders' => $purchaseOrders,
            'availableProducts' => $products,
            'grnNumber' => $this->generateGoodReceiveNoteNumber(),
        ]);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'goods_received_note_no'   => 'required|string|unique:goods_received_notes,goods_received_note_no',
            'supplier_id'   => 'required|exists:suppliers,id',
            'goods_received_note_date'      => 'required|date',
            'purchase_order_request_id'        => 'nullable|exists:purchase_order_requests,id',
            'discount'      => 'nullable|numeric|min:0',
            'tax_total'     => 'nullable|numeric|min:0',
            'remarks'       => 'nullable|string',

            'products'                      => 'required|array|min:1',
            'products.*.product_id'         => 'required|exists:products,id',
            'products.*.requested_quantity' => 'required|numeric|min:0.01',
            'products.*.issued_quantity'    => 'required|numeric|min:0.01',
            'products.*.purchase_price'     => 'required|numeric|min:0',
            'products.*.discount'           => 'nullable|numeric|min:0',
            'products.*.unit'               => 'nullable|string',
            'products.*.total'              => 'nullable|numeric',
        ]);

        DB::beginTransaction();

        try {
            $grn = GoodsReceivedNote::create([
                'purchase_order_request_id'        => $validated['purchase_order_request_id'] ?? null,
                'goods_received_note_no'        => $validated['goods_received_note_no'],
                'supplier_id'   => $validated['supplier_id'],
                'user_id'       => auth()->id(),
                'goods_received_note_date'      => $validated['goods_received_note_date'],
                'discount'      => $validated['discount'] ?? 0,
                'tax_total'     => $validated['tax_total'] ?? 0,
                'remarks'       => $validated['remarks'] ?? null,
                'status'        => 1,
            ]);

            foreach ($validated['products'] as $product) {
                $lineTotal = ((float)($product['issued_quantity']) * (float)($product['purchase_price'])) - ((float)($product['discount'] ?? 0));

                // Save received product line (store quantity in `quantity` column)
                GoodsReceivedNoteProduct::create([
                    'goods_received_note_id' => $grn->id,
                    'product_id'            => $product['product_id'],
                    // store received amount in `quantity` using requested_quantity coming from the frontend
                    'quantity'              => (int) $product['issued_quantity'],
                    'purchase_price'        => $product['purchase_price'],
                    'discount'              => $product['discount'] ?? 0,
                    'total'                 => $lineTotal,
                ]);

                // Record product movement (Purchase - increases stock)
                ProductMovement::recordMovement(
                    $product['product_id'],
                    ProductMovement::TYPE_PURCHASE,
                    (int) $product['issued_quantity'], // Positive for stock increase
                    $validated['goods_received_note_no']
                );

                // Increment storage stock quantity on the product by the received amount
                Product::where('id', $product['product_id'])
                    ->increment('store_quantity', (int) $product['issued_quantity']);

                // If this GRN is linked to a Purchase Order Request, update the issued_quantity on that POR's product row
                if (!empty($grn->purchase_order_request_id)) {
                    PurchaseOrderRequestProduct::where('purchase_order_request_id', $grn->purchase_order_request_id)
                        ->where('product_id', $product['product_id'])
                        ->increment('issued_quantity', (int) $product['issued_quantity']);
                }
            }

            // If this GRN is linked to a Purchase Order Request, update the PO status:
            // - If all POR products have issued_quantity >= requested_quantity -> 'completed'
            // - Otherwise -> 'processing' (this covers the first GRN for a PO)
            if (!empty($grn->purchase_order_request_id)) {
                $poId = $grn->purchase_order_request_id;
                $porProducts = PurchaseOrderRequestProduct::where('purchase_order_request_id', $poId)->get();

                $allCompleted = $porProducts->every(function ($p) {
                    $issued = $p->issued_quantity ?? 0;
                    $requested = $p->requested_quantity ?? 0;
                    return $issued >= $requested;
                });

                $po = PurchaseOrderRequest::find($poId);

                if ($allCompleted) {
                    if ($po && $po->status !== 'completed') {
                        $po->update(['status' => 'completed']);
                    }
                } else {
                    // If this is the first GRN (i.e. PO isn't already processing/completed), mark it processing
                    if ($po && !in_array($po->status, ['processing', 'completed'])) {
                        $po->update(['status' => 'processing']);
                    }
                }
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

    // public function update(Request $request, GoodsReceivedNote $goodsReceivedNote)
    // {
    //     $validated = $request->validate([
    //         'goods_received_note_no'        => 'required|unique:goods_received_notes,goods_received_note_no,' . $goodsReceivedNote->id,
    //         'supplier_id'   => 'required|exists:suppliers,id',
    //         'goods_received_note_date'      => 'required|date',
    //         'purchase_order_request_id'        => 'nullable|exists:purchase_order_requests,id',
    //         'discount'      => 'nullable|numeric|min:0',
    //         'tax_total'     => 'nullable|numeric|min:0',
    //         'remarks'       => 'nullable|string',
    //         'status'        => 'required|in:0,1,2',
    //     ]);

    //     DB::beginTransaction();

    //     try {
    //         $goodsReceivedNote->update([
    //             'goods_received_note_no'        => $validated['goods_received_note_no'],
    //             'supplier_id'   => $validated['supplier_id'],
    //             'goods_received_note_date'      => $validated['goods_received_note_date'],
    //             'purchase_order_request_id'        => $validated['purchase_order_request_id'] ?? null,
    //             'discount'      => $validated['discount'] ?? 0,
    //             'tax_total'     => $validated['tax_total'] ?? 0,
    //             'remarks'       => $validated['remarks'] ?? null,
    //             'status'        => $validated['status'],
    //         ]);

    //         DB::commit();

    //         return redirect()->route('good-receive-notes.index')->with('success', 'Good Receive Note updated successfully');

    //     } catch (\Throwable $e) {
    //         DB::rollBack();

    //         return redirect()
    //             ->back()
    //             ->withErrors(['error' => 'Failed to update Good Receive Note: ' . $e->getMessage()])
    //             ->withInput();
    //     }
    // }

    public function updateStatus(Request $request, GoodsReceivedNote $goodReceiveNote)
    {
        $validated = $request->validate([
            'status' => 'required|in:0,1,2',
        ]);

        $goodReceiveNote->update(['status' => $validated['status']]);

        return redirect()->back()->with('success', 'Status updated successfully');
    }

            public function destroy(GoodsReceivedNote $goodsReceivedNote)
    {
        $goodsReceivedNote->update(['status' => 0]);

        return redirect()->back()->with('success', 'Good Receive Note marked as inactive successfully');
    }

    
    private function generateGoodReceiveNoteNumber()
    {
        $prefix = 'GRN';
        $date = date('Ymd');

        // Find last GRN created today
        $lastGrn = GoodsReceivedNote::whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();

        // Extract last sequence
        $sequence = 1;
        if ($lastGrn && !empty($lastGrn->goods_received_note_no)) {
            $parts = explode('-', $lastGrn->goods_received_note_no);
            if (count($parts) >= 3) {
                $lastSeq = (int) $parts[2];
                $sequence = $lastSeq + 1;
            }
        }

        // Return final GRN number
        return $prefix . '-' . $date . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }




}
