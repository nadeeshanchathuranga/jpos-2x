<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseExpenseController extends Controller
{
    public function index()
    {
        $expenses = Expense::with(['user', 'supplier'])
            ->orderBy('expense_date', 'desc')
            ->paginate(10);

        $suppliers = Supplier::where('status', 1)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return Inertia::render('PurchaseExpenses/Index', [
            'expenses' => $expenses,
            'suppliers' => $suppliers,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'payment_type' => 'required|integer|in:0,1,2,3',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'reference' => 'required_if:payment_type,1,3|nullable|string|max:255',
        ]);

        $validated['user_id'] = Auth::id();

        Expense::create($validated);

        return redirect()->route('purchase-expenses.index')
            ->with('success', 'Purchase Expense created successfully.');
    }

    public function update(Request $request, Expense $purchaseExpense)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'payment_type' => 'required|integer|in:0,1,2,3',
            'reference' => 'required_if:payment_type,1,3|nullable|string|max:255',
        ]);

        $purchaseExpense->update($validated);

        return redirect()->route('purchase-expenses.index')
            ->with('success', 'Purchase Expense updated successfully.');
    }

    public function destroy(Expense $purchaseExpense)
    {
        $purchaseExpense->delete();

        return redirect()->route('purchase-expenses.index')
            ->with('success', 'Purchase Expense deleted successfully.');
    }

    public function getSupplierData(Request $request)
    {
        $supplierId = $request->input('supplier_id');
        
        // Calculate total amount from GRN products
        $totalAmount = DB::table('goods_received_notes_products')
            ->join('goods_received_notes', 'goods_received_notes_products.goods_received_note_id', '=', 'goods_received_notes.id')
            ->where('goods_received_notes.supplier_id', $supplierId)
            ->sum(DB::raw('CAST(goods_received_notes_products.total as DECIMAL(15,2))'));
        
        // Convert to float
        $totalAmount = (float) ($totalAmount ?? 0);
        
        // Calculate paid amount from expenses for this supplier
        $paid = DB::table('purchase_expenses')
            ->where('supplier_id', $supplierId)
            ->sum(DB::raw('CAST(amount as DECIMAL(15,2))'));
        
        $paid = (float) ($paid ?? 0);
        
        // Calculate balance
        $balance = $totalAmount - $paid;
        
        return response()->json([
            'total_amount' => number_format($totalAmount, 2, '.', ''),
            'paid' => number_format($paid, 2, '.', ''),
            'balance' => number_format($balance, 2, '.', ''),
        ]);
    }
}
