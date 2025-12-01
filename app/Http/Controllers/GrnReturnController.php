<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGrnReturnRequest;
use App\Models\GrnReturn;
use App\Models\GrnReturnProduct;
use App\Models\Grn;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class GrnReturnController extends Controller
{
    public function index()
    {
        $returns = GrnReturn::with(['user', 'grn', 'grn_return_products.product'])->latest()->paginate(20);
        $grns = Grn::orderByDesc('id')->get(['id','grn_no','supplier_id']);
        // Debug logging: help trace why frontend might receive empty `grns`
        // try {
        //     Log::info('GrnReturnController@index - grns count: ' . $grns->count());
        //     Log::info('GrnReturnController@index - sample grns: ' . $grns->take(5)->map(fn($g) => $g->grn_no)->implode(', '));
        // } catch (\Throwable $e) {
        //     Log::error('GrnReturnController@index - failed logging grns: ' . $e->getMessage());
        // }
        $user = auth()->user();
        return Inertia::render('GrnReturns/Index', compact('returns', 'grns', 'user'));
    }

    public function create()
    {
        $grns = Grn::orderByDesc('id')->get(['id','grn_no','supplier_id']);
        // try {
        //     Log::info('GrnReturnController@create - grns count: ' . $grns->count());
        //     Log::info('GrnReturnController@create - sample grns: ' . $grns->take(5)->map(fn($g) => $g->grn_no)->implode(', '));
        // } catch (\Throwable $e) {
        //     Log::error('GrnReturnController@create - failed logging grns: ' . $e->getMessage());
        // }
        $products = Product::orderBy('name')->get();
        $user = auth()->user();
        return Inertia::render('GrnReturns/Create',[ 
        'grns' => $grns,
        'products' => $products,
        'user' => $user,
        ]);
    }

    public function store(StoreGrnReturnRequest $request)
    {
        $data = $request->validated();

        DB::beginTransaction();
        try {
            $grnReturn = GrnReturn::create([
                'grn_id' => $data['grn_id'],
                'date' => $data['date'],
                'user_id' => $data['user_id'],
            ]);

            foreach ($data['products'] as $p) {
                GrnReturnProduct::create([
                    'grn_return_id' => $grnReturn->id,
                    'products_id' => $p['product_id'],
                    'qty' => $p['qty'],
                    'remarks' => $p['remarks'] ?? null,
                ]);
            }

            DB::commit();
            return redirect()->route('grn-returns.index')->with('success', 'GRN return recorded.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
