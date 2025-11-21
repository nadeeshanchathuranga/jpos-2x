<?php

namespace App\Http\Controllers;

use App\Models\Por;
use App\Models\PorProduct;
use App\Models\MeasurementUnit;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PorController extends Controller
{
    /**
     * Display a listing of PORs
     */
    public function index()
    {
        $pors = Por::with(['products.product.measurementUnit', 'user','measurementUnit'])
            ->latest()
            ->paginate(10);

      
        $products = Product::with('measurementUnit')
            ->where('status', '!=', 0)
            ->get();
        
        $measurementUnits = MeasurementUnit::orderBy('name')
            ->get();

        $users = User::orderBy('name')->get();

   

        $orderNumber = $this->generateOrderNumber();

        return Inertia::render('Por/Index', [
            'pors' => $pors,
            'products' => $products,
            'measurementUnits' => $measurementUnits,
            'users' => $users,
            'orderNumber' => $orderNumber
        ]);
    }

    /**
     * Show the form for creating a new POR
     */
    public function create()
    {
        return Inertia::render('Por/Create', [
            'products' => Product::with(['measurementUnit', 'measurementUnits'])
                ->where('status', 'active')
                ->get(),
            'measurementUnits' => MeasurementUnit::all(),
            'orderNumber' => $this->generateOrderNumber(),
        ]);
    }

    /**
     * Store a newly created POR
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_number' => 'required|string|unique:pors,order_number',
            'order_date' => 'required|date',
            'user_id' => 'required|exists:users,id',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.measurement_unit_id' => 'required|exists:measurement_units,id'
        ]);

        DB::beginTransaction();
        
        try {
            $por = Por::create([
                'order_number' => $validated['order_number'],
                'order_date' => $validated['order_date'],
                'user_id' => $validated['user_id'],
                'total_amount' => 0,
                'status' => 'pending',
                'created_by' => Auth::id()
            ]);

            foreach ($validated['products'] as $productData) {
                PorProduct::create([
                    'por_id' => $por->id,
                    'product_id' => $productData['product_id'],
                    'quantity' => $productData['quantity'],
                    'measurement_unit_id' => $productData['measurement_unit_id']
                ]);
            }

            DB::commit();

            return redirect()->route('por.index')
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
    public function show(Por $por)
    {
        $por->load('porProducts');
        
        return Inertia::render('Por/Show', [
            'por' => $por
        ]);
    }

    /**
     * Update the status of the specified POR
     */
    public function updateStatus(Request $request, Por $por)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected,completed'
        ]);
        
        $por->update(['status' => $request->status]);
        
        return back()->with('success', 'Status updated successfully');
    }

    private function generateOrderNumber()
    {
        $prefix = 'POR';
        $date = date('Ymd');
        $lastPor = Por::whereDate('created_at', today())
            ->latest()
            ->first();
        
        $sequence = $lastPor ? (int)substr($lastPor->order_number, -4) + 1 : 1;
        
        return $prefix . '-' . $date . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
