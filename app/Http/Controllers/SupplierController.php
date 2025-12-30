<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $suppliers = Supplier::orderBy('id', 'desc')

            ->paginate(10);

        return Inertia::render('Suppliers/Index', [
            'suppliers' => $suppliers,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

       $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'nullable|email:rfc,dns|max:255',   // Better email check
            'phone_number'     => 'nullable|regex:/^[0-9+\-\s()]{7,20}$/|max:20',
            'address'   => 'nullable|string',
            'status'    => 'required|in:0,1',
        ]);

        Supplier::create($validated);

        return redirect()->back()->with('success', 'Supplier created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Supplier $supplier)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Supplier $supplier)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
             'name'      => 'required|string|max:255',
            'email'     => 'nullable|email:rfc,dns|max:255',   // Better email check
            'phone_number'     => 'nullable|regex:/^[0-9+\-\s()]{7,20}$/|max:20', // phone_number pattern
            'address'   => 'nullable|string',
            'status'    => 'required|in:0,1',
        ]);

        $supplier->update($validated);

        return redirect()->back()->with('success', 'Supplier updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier)
    {
        $supplier->update(['status' => 0]);

        return redirect()->back()->with('success', 'Supplier deleted successfully');
    }
}
