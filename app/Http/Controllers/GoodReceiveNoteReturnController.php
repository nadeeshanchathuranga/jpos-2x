<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GoodsReceivedNoteReturn;
use App\Models\GoodsReceivedNoteReturnProduct;
use App\Models\GoodsReceivedNote;
use App\Models\Product;
use App\Models\ProductMovement;
use App\Models\MeasurementUnit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class GoodReceiveNoteReturnController extends Controller
{
    public function index()
    {
        // eager-load GRN and its products so the view can reference original GRN quantities
        $returns = GoodsReceivedNoteReturn::with(['user', 'goodsReceivedNote.grnProducts.product', 'goodsReceivedNoteReturnProducts.product'])->latest()->paginate(20);
        // eager-load GRN products so frontend can autofill on selection
        // serialize to plain array to avoid V8/proxy serialization differences in Inertia
        $goodsReceivedNotes = GoodsReceivedNote::with(['grnProducts.product'])->orderByDesc('id')->get()->toArray();
        $user = auth()->user();
        // load available products and measurement units for the frontend
        $availableProducts = Product::where('status', '!=', 0)->orderBy('name')->get();
        // ensure measurement units are serialized as a plain array for Inertia
        $measurementUnits = MeasurementUnit::orderBy('name')->get()->toArray();

        return Inertia::render('GoodReceiveNoteReturns/Index', compact('returns', 'goodsReceivedNotes', 'user', 'availableProducts', 'measurementUnits'));
    }

    public function create()
    {
        // include grnProducts so frontend can autofill products without extra routes
        // serialize to plain array for predictable client-side shape
        $goodsReceivedNotes = GoodsReceivedNote::with(['grnProducts.product'])->orderByDesc('id')->get()->toArray();
        $products = Product::orderBy('name')->get();
        $measurementUnits = MeasurementUnit::orderBy('name')->get()->toArray();
        $user = auth()->user();
        return Inertia::render('goodsReceivedNoteReturns/Create',[ 
        'goodsReceivedNotes' => $goodsReceivedNotes,
        'products' => $products,
        'measurementUnits' => $measurementUnits,
        'user' => $user,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'goods_received_note_id' => 'required|exists:goods_received_notes,id',
            'date' => 'required|date',
            'user_id' => 'required|exists:users,id',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.qty' => 'required|numeric|min:1',
            'products.*.remarks' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $goodReceiveNoteReturn = GoodsReceivedNoteReturn::create([
                'goods_received_note_id' => $validated['goods_received_note_id'],
                'date' => $validated['date'],
                'user_id' => $validated['user_id'],
            ]);

            foreach ($validated['products'] as $p) {
                GoodsReceivedNoteReturnProduct::create([
                    'goods_received_note_return_id' => $goodReceiveNoteReturn->id,
                    // DB column is `products_id` (plural) per migration/model
                    'products_id' => $p['product_id'],
                    'qty' => $p['qty'],
                    'remarks' => $p['remarks'] ?? null,
                ]);
                // record product movement for BRN return (type 5)
                if (!empty($p['quantity']) && $p['quantity'] > 0) {
                    ProductMovement::record($p['product_id'], ProductMovement::TYPE_GRN_RETURN, $p['quantity'], 'GRN Return #' . $grnReturn->id);
                    // decrement storage stock quantity
                    $product = Product::find($p['product_id']);
                    if ($product) {
                        // ensure numeric
                        $quantity = is_numeric($p['qty']) ? (float)$p['qty'] : floatval($p['qty']);
                        // convert purchase unit -> transfer -> sales unit
                        $purchaseToTransfer = is_numeric($product->purchase_to_transfer_rate) && $product->purchase_to_transfer_rate > 0 ? (float)$product->purchase_to_transfer_rate : 1.0;
                        $transferToSales = is_numeric($product->transfer_to_sales_rate) && $product->transfer_to_sales_rate > 0 ? (float)$product->transfer_to_sales_rate : 1.0;
                        $converted = $quantity * $purchaseToTransfer * $transferToSales;
                        // round to 4 decimal places to avoid tiny fractions
                        $converted = round($converted, 4);
                        $product->increment('store_quantity', $converted);
                    }
                }
            }

            DB::commit();
            return redirect()->route('good-receive-note-returns.index')->with('success', 'Good receive note return recorded.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function update(Request $request, GoodsReceivedNoteReturn $goodReceiveNoteReturn)
    {
        $validated = $request->validate([
            'goods_received_note_id' => 'required|exists:goods_received_notes,id',
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
                'good_receive_note_return_id' => $validated['good_receive_note_id'],
                'date' => $validated['date'],
                'user_id' => $validated['user_id'],
            ]);

            // restore stock and remove previous product movements for this return
            $existing = GoodsReceivedNoteReturnProduct::where('goods_received_note_return_id', $goodReceiveNoteReturn->id)->get();
            foreach ($existing as $ex) {
                // add back previously subtracted qty (convert to sale unit)
                $product = Product::find($ex->products_id);
                if ($product) {
                    $quantity = is_numeric($ex->qty) ? (float)$ex->qty : floatval($ex->qty);
                    $purchaseToTransfer = is_numeric($product->purchase_to_transfer_rate) && $product->purchase_to_transfer_rate > 0 ? (float)$product->purchase_to_transfer_rate : 1.0;
                    $transferToSales = is_numeric($product->transfer_to_sales_rate) && $product->transfer_to_sales_rate > 0 ? (float)$product->transfer_to_sales_rate : 1.0;
                    $converted = round($quantity * $purchaseToTransfer * $transferToSales, 4);
                    $product->decrement('store_quantity', $converted);
                }
            }
            // delete previous product movement records tied to this GRN return (by reference)
            ProductMovement::where('reference', 'Good Receive Note Return #' . $goodReceiveNoteReturn->id)->delete();

            // remove existing product rows and recreate
            GoodsReceivedNoteReturnProduct::where('goods_received_note_return_id', $goodReceiveNoteReturn->id)->delete();

            foreach ($validated['products'] as $p) {
                GoodsReceivedNoteReturnProduct::create([
                    'goods_received_note_return_id' => $goodReceiveNoteReturn->id,
                    'products_id' => $p['product_id'],
                    'qty' => $p['qty'],
                    'remarks' => $p['remarks'] ?? null,
                ]);
                // record product movement for BRN return (type 5) and decrement stock
                if (!empty($p['qty']) && $p['qty'] > 0) {
                    ProductMovement::record($p['product_id'], ProductMovement::TYPE_GRN_RETURN, $p['qty'], 'Good Receive Note Return #' . $goodReceiveNoteReturn->id);
                    $product = Product::find($p['product_id']);
                    if ($product) {
                        $quantity = is_numeric($p['qty']) ? (float)$p['qty'] : floatval($p['qty']);
                        $purchaseToTransfer = is_numeric($product->purchase_to_transfer_rate) && $product->purchase_to_transfer_rate > 0 ? (float)$product->purchase_to_transfer_rate : 1.0;
                        $transferToSales = is_numeric($product->transfer_to_sales_rate) && $product->transfer_to_sales_rate > 0 ? (float)$product->transfer_to_sales_rate : 1.0;
                        $converted = round($quantity * $purchaseToTransfer * $transferToSales, 4);
                        $product->decrement('store_quantity', $converted);
                    }
                }
            }

            DB::commit();
            return redirect()->route('good-receive-note-returns.index')->with('success', 'Good Receive Note return updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed updating Good Receive Note return: ' . $e->getMessage());
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy(GoodsReceivedNoteReturn $goodReceiveNoteReturn)
    {
        DB::beginTransaction();
        try {
            // restore stock for related products and remove related product movements
            $existing = GoodsReceivedNoteReturnProduct::where('goods_received_note_return_id', $goodReceiveNoteReturn->id)->get();
            foreach ($existing as $ex) {
                $product = Product::find($ex->products_id);
                if ($product) {
                    $qty = is_numeric($ex->qty) ? (float)$ex->qty : floatval($ex->qty);
                    $purchaseToTransfer = is_numeric($product->purchase_to_transfer_rate) && $product->purchase_to_transfer_rate > 0 ? (float)$product->purchase_to_transfer_rate : 1.0;
                    $transferToSales = is_numeric($product->transfer_to_sales_rate) && $product->transfer_to_sales_rate > 0 ? (float)$product->transfer_to_sales_rate : 1.0;
                    $converted = round($qty * $purchaseToTransfer * $transferToSales, 4);
                    $product->increment('store_quantity', $converted);
                }
            }

            // delete previous product movement records tied to this GRN return (by reference)
            ProductMovement::where('reference', 'Good Receive Note Return #' . $goodReceiveNoteReturn->id)->delete();

            // delete related products
            GoodsReceivedNoteReturnProduct::where('goods_received_note_return_id', $goodReceiveNoteReturn->id)->delete();

            // delete the return
            $goodReceiveNoteReturn->delete();

            DB::commit();
            return redirect()->route('good-receive-note-returns.index')->with('success', 'Good Receive Note return deleted.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed deleting Good Receive Note return: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
