<?php

namespace App\Http\Controllers;

use App\Models\Tax;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaxController extends Controller
{
    public function index()
    {
        $taxes = Tax::orderBy('status', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return Inertia::render('Taxes/Index', [
            'taxes' => $taxes,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'percentage' => 'required|numeric|min:0|max:100',
            'type' => 'required|in:0,1',
            'status' => 'required|in:0,1',
        ]);

        Tax::create($validated);

        return redirect()->back()->with('success', 'Tax created successfully');
    }

    public function update(Request $request, Tax $tax)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'percentage' => 'required|numeric|min:0|max:100',
            'type' => 'required|in:0,1',
            'status' => 'required|in:0,1',
        ]);

        $tax->update($validated);

        return redirect()->back()->with('success', 'Tax updated successfully');
    }

    public function destroy(Tax $tax)
    {
        $tax->update(['status' => 0]);

        return redirect()->back()->with('success', 'Tax deleted successfully');
    }
}
