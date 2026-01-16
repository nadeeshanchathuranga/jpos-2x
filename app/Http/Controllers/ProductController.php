<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Type;
use App\Models\MeasurementUnit;
use App\Models\CompanyInformation;
use App\Models\Discount;
use App\Models\Tax;
use App\Models\Unit;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProductController extends Controller
{
    /**
     * Generate unique barcode
     */
    private function generateBarcode()
    {
        do {
            // Generate 13 digit barcode (EAN-13 format)
            $barcode = '2' . str_pad(rand(0, 999999999999), 12, '0', STR_PAD_LEFT);
        } while (Product::where('barcode', $barcode)->exists());

        return $barcode;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
 $products = Product::with([
        'brand',
        'category',
        'type',
        'discount',
        'tax',
        'purchaseUnit',
        'salesUnit',
        'transferUnit'
    ])
    ->orderBy('id', 'desc')
    ->paginate(10);


      $brands = Brand::where('status', '!=', 0)
    ->orderBy('id', 'desc')
    ->get();

$categories = Category::where('status', '!=', 0)
    ->orderBy('id', 'desc')
    ->get();

$types = Type::where('status', '!=', 0)
    ->orderBy('id', 'desc')
    ->get();

$measurementUnits = MeasurementUnit::where('status', '!=', 0)
    ->orderBy('id', 'desc')
    ->get();

$discounts = Discount::where('status', '!=', 0)
    ->orderBy('id', 'desc')
    ->get();

$taxes = Tax::where('status', '!=', 0)
    ->orderBy('id', 'desc')
    ->get();

$currencySymbol = CompanyInformation::first();


        return Inertia::render('Products/Index', [
            'products' => $products,
            'brands' => $brands,
            'categories' => $categories,
            'types' => $types,
            'measurementUnits' => $measurementUnits,
            'discounts' => $discounts,
            'currencySymbol' => $currencySymbol,
            'taxes' => $taxes,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $brands = Brand::all();
        $categories = Category::all();
        $types = Type::all();
        $measurementUnits = MeasurementUnit::where('status', '!=', 0)->get();
        $discounts = Discount::all();
        $taxes = Tax::all();
        $units = Unit::all();

        return Inertia::render('Products/Create', [
            'brands' => $brands,
            'categories' => $categories,
            'types' => $types,
            'discounts' => $discounts,
            'taxes' => $taxes,
            'measurementUnits' => $measurementUnits,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'barcode' => 'nullable|string|unique:products,barcode',
        'brand_id' => 'nullable|exists:brands,id',
        'category_id' => 'nullable|exists:categories,id',
        'type_id' => 'nullable|exists:types,id',
        'discount_id' => 'nullable|exists:discounts,id',
        'tax_id' => 'nullable|exists:taxes,id',

        'shop_quantity' => 'required|numeric|min:0',
        'shop_low_stock_margin' => 'nullable|numeric|min:0',


        'store_quantity' => 'nullable|numeric|min:0',
        'store_low_stock_margin' => 'nullable|numeric|min:0',

        'purchase_price' => 'required|numeric|min:0',
        'wholesale_price' => 'nullable|numeric|min:0',
        'retail_price' => 'required|numeric|min:0',

        'return_product' => 'nullable|boolean',

        'purchase_unit_id' => 'nullable|exists:measurement_units,id',
        'sales_unit_id' => 'nullable|exists:measurement_units,id',
        'transfer_unit_id' => 'nullable|exists:measurement_units,id',

        'purchase_to_transfer_rate' => 'nullable|numeric|min:0',
        'transfer_to_sales_rate' => 'nullable|numeric|min:0',

        'status' => 'required|integer|in:0,1',

        'image' => 'nullable|image|max:2048',
    ]);

    // Generate barcode if empty
    if (empty($validated['barcode'])) {
        $validated['barcode'] = $this->generateBarcode();
    }

    // Image upload
    if ($request->hasFile('image')) {
        $validated['image'] = $request->file('image')->store('products', 'public');
    }

    // Return product convert to boolean
    $validated['return_product'] = $request->boolean('return_product');

    /*-------------------------------------------------------
     |  UNIT CONVERSION LOGIC
     |  store_quantity (purchase unit) → sales units
     |------------------------------------------------------*/

    $storeQty = $validated['store_quantity'] ?? 0;
    $ratePT   = $validated['purchase_to_transfer_rate'] ?? 0;
    $rateTS   = $validated['transfer_to_sales_rate'] ?? 0;

    // Convert: purchase → transfer
    $transferQty = $storeQty * $ratePT;

    // Convert: transfer → sales
    $salesQty = $transferQty * $rateTS;

    // 🔥 Replace store_quantity with final converted sales units
    $validated['store_quantity'] = $salesQty;



    Product::create($validated);

    return redirect()->route('products.index')
        ->with('success', 'Product created successfully.');
}


    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $brands = Brand::all();
        $categories = Category::all();
        $types = Type::all();
        $measurementUnits = MeasurementUnit::where('status', '!=', 0)->get();
        $discounts = Discount::all();
        $taxes = Tax::all();
        $units = Unit::all();

        return Inertia::render('Products/Edit', [
            'product' => $product,
            'brands' => $brands,
            'categories' => $categories,
            'types' => $types,
            'discounts' => $discounts,
            'taxes' => $taxes,
            'measurementUnits' => $measurementUnits,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'barcode' => 'nullable|string|unique:products,barcode,' . $product->id,
            'brand_id' => 'nullable|exists:brands,id',
            'category_id' => 'nullable|exists:categories,id',
            'type_id' => 'nullable|exists:types,id',
            'discount_id' => 'nullable|exists:discounts,id',
            'tax_id' => 'nullable|exists:taxes,id',
            'shop_quantity' => 'required|numeric|min:0',
            'shop_low_stock_margin' => 'nullable|numeric|min:0',

            'store_quantity' => 'nullable|numeric|min:0',
            'store_low_stock_margin' => 'nullable|numeric|min:0',

            'purchase_price' => 'required|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'retail_price' => 'required|numeric|min:0',
            'return_product' => 'nullable|boolean',
            'purchase_unit_id' => 'nullable|exists:measurement_units,id',
            'sales_unit_id' => 'nullable|exists:measurement_units,id',
            'transfer_unit_id' => 'nullable|exists:measurement_units,id',
            'purchase_to_transfer_rate' => 'nullable|numeric|min:0',
            'transfer_to_sales_rate' => 'nullable|numeric|min:0',
            'store_in_transfer_units' => 'nullable|numeric|min:0',
            'store_in_sales_units' => 'nullable|numeric|min:0',
            'shop_in_transfer_units' => 'nullable|numeric|min:0',
            'shop_in_purchase_units' => 'nullable|numeric|min:0',
            'status' => 'required|integer|in:0,1',
            'image' => 'nullable|image|max:2048',
        ]);

        // Generate barcode if product doesn't have one
        if (empty($product->barcode) && empty($validated['barcode'])) {
            $validated['barcode'] = $this->generateBarcode();
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        // Convert return_product to boolean
        $validated['return_product'] = $request->boolean('return_product');

        /*-------------------------------------------------------
         |  UNIT CONVERSION LOGIC (for updates)
         |  Convert submitted store_quantity (purchase units)
         |  into final sales units before saving — same logic
         |  as used in the `store` and `duplicate` methods.
         |------------------------------------------------------*/
        $storeQty = $validated['store_quantity'] ?? 0;
        $ratePT   = $validated['purchase_to_transfer_rate'] ?? 0;
        $rateTS   = $validated['transfer_to_sales_rate'] ?? 0;
        $transferQty = $storeQty * $ratePT;
        $salesQty    = $transferQty * $rateTS;
        $validated['store_quantity'] = $salesQty;

        $product->update($validated);

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        try {
            // Set status to inactive before soft deleting
            $product->status = 0;
            $product->save();

            // Soft delete the product
            $product->delete();

            return redirect()->route('products.index')->with('success', 'Product deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to delete product: ' . $e->getMessage()]);
        }
    }

    /**
     * Duplicate a product
     */
    public function duplicate(Request $request, Product $product)
    {


        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'barcode' => 'nullable|string|unique:products,barcode',
            'brand_id' => 'nullable|exists:brands,id',
            'category_id' => 'nullable|exists:categories,id',
            'type_id' => 'nullable|exists:types,id',
            'discount_id' => 'nullable|exists:discounts,id',
            'tax_id' => 'nullable|exists:taxes,id',
            'shop_quantity' => 'required|numeric|min:0',
            'shop_low_stock_margin' => 'nullable|numeric|min:0',

            'store_quantity' => 'nullable|numeric|min:0',
            'store_low_stock_margin' => 'nullable|numeric|min:0',

            'purchase_price' => 'required|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'retail_price' => 'required|numeric|min:0',
            'return_product' => 'nullable|boolean',
            'purchase_unit_id' => 'nullable|exists:measurement_units,id',
            'sales_unit_id' => 'nullable|exists:measurement_units,id',
            'transfer_unit_id' => 'nullable|exists:measurement_units,id',
            'purchase_to_transfer_rate' => 'nullable|numeric|min:0',
            'transfer_to_sales_rate' => 'nullable|numeric|min:0',

            'status' => 'required|integer|in:0,1',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Auto-generate barcode if not provided
        if (empty($validated['barcode'])) {
            $validated['barcode'] = $this->generateBarcode();
        }

        // Image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        // Boolean cast
        $validated['return_product'] = $request->boolean('return_product');

        // Unit conversion: purchase → transfer → sales
        $storeQty = $validated['store_quantity'] ?? 0;
        $ratePT   = $validated['purchase_to_transfer_rate'] ?? 0;
        $rateTS   = $validated['transfer_to_sales_rate'] ?? 0;
        $transferQty = $storeQty * $ratePT;
        $salesQty    = $transferQty * $rateTS;
        $validated['store_quantity'] = $salesQty;

        Product::create($validated);

        return redirect()->route('products.index')->with('success', 'Product duplicated successfully!');
    }

    /**
     * Log activity to activity_logs table
     */
    public function logActivity(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|string',
            'module' => 'required|string',
            'details' => 'required',
        ]);

        // Accept details as string or array
        $details = $validated['details'];
        if (is_array($details) || is_object($details)) {
            $details = json_encode($details);
        }

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $validated['action'],
            'module' => $validated['module'],
            'details' => $details,
        ]);

        return response()->json(['success' => true]);
    }
}
