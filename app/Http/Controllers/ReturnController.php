<?php

namespace App\Http\Controllers;

use App\Models\SalesReturn;
use App\Models\SalesReturnProduct;
use App\Models\Sale;
use App\Models\SalesProduct;
use App\Models\Product;
use App\Models\Customer;
use App\Models\ProductMovement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReturnController extends Controller
{
    /**
     * Display a listing of all returns and sales products available for return
     */
    public function index(Request $request)
    {

         
        $query = SalesReturn::with([
            'products.product' => function($query) {
                $query->select('id', 'name', 'barcode', 'return_product');
            },
            'sale' => function($query) {
                $query->select('id', 'invoice_no');
            },
            'customer' => function($query) {
                $query->select('id', 'name', 'phone_number');
            },
            'user' => function($query) {
                $query->select('id', 'name');
            }
        ]);

        // Filter by status if provided
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by date range if provided
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('return_date', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('return_date', '<=', $request->date_to);
        }

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('customer', function($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                          ->orWhere('phone_number', 'like', "%{$search}%");
                })
                ->orWhereHas('sale', function($query) use ($search) {
                    $query->where('invoice_no', 'like', "%{$search}%");
                })
                ->orWhereHas('products.product', function($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                          ->orWhere('barcode', 'like', "%{$search}%");
                });
            });
        }

        $returns = $query->orderBy('return_date', 'desc')
                        ->orderBy('id', 'desc')
                        ->paginate(15)
                        ->withQueryString();

        // Add computed fields
        $returns->through(function ($return) {
            return [
                'id' => $return->id,
                'return_no' => 'RET-' . str_pad($return->id, 5, '0', STR_PAD_LEFT),
                'sale_id' => $return->sale_id,
                'sale_no' => $return->sale?->invoice_no,
                'customer_id' => $return->customer_id,
                'customer_name' => $return->customer?->name,
                'customer_phone' => $return->customer?->phone_number,
                'user_name' => $return->user?->name,
                'return_date' => $return->return_date ? \Carbon\Carbon::parse($return->return_date)->format('Y-m-d') : null,
                'return_date_formatted' => $return->return_date ? \Carbon\Carbon::parse($return->return_date)->format('M d, Y') : 'N/A',
                'status' => $return->status,
                'status_text' => $return->status_text,
                'status_color' => $return->status_color,
                'total_refund' => $return->total_refund,
                'total_refund_formatted' => number_format($return->total_refund, 2),
                'products_count' => $return->products->count(),
                'returnable_products_count' => $return->products->filter(function($item) {
                    return $item->product?->return_product == true;
                })->count(),
                'return_products' => $return->products->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product?->name,
                        'product_barcode' => $item->product?->barcode,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'total' => $item->total,
                        'is_returnable' => $item->product?->return_product == true,
                        'formatted_price' => number_format((float)$item->price, 2),
                        'formatted_total' => number_format((float)$item->total, 2),
                    ];
                }),
                'products' => $return->products->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product?->name,
                        'product_barcode' => $item->product?->barcode,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'total' => $item->total,
                        'is_returnable' => $item->product?->return_product == true,
                        'formatted_price' => number_format((float)$item->price, 2),
                        'formatted_total' => number_format((float)$item->total, 2),
                    ];
                }),
            ];
        });

        // Get sales products available for return (last 30 days by default)
        $salesProducts = $this->getAvailableSalesProducts($request);

        return Inertia::render('Returns/Index', [
            'returns' => $returns,
            'salesProducts' => $salesProducts,
            'filters' => $request->only(['status', 'search', 'date_from', 'date_to']),
            'statusOptions' => [
                ['value' => SalesReturn::STATUS_PENDING, 'label' => 'Pending'],
                ['value' => SalesReturn::STATUS_APPROVED, 'label' => 'Approved'],
                ['value' => SalesReturn::STATUS_REJECTED, 'label' => 'Rejected'],
            ]
        ]);
    }

    /**
     * Update return status
     */
    public function updateStatus(Request $request, SalesReturn $return)
    {
        $request->validate([
            'status' => 'required|in:0,1,2'
        ]);

        DB::transaction(function () use ($request, $return) {
            $oldStatus = $return->status;
            $newStatus = $request->status;

            // Update return status
            $return->update([
                'status' => $newStatus
            ]);

            // If status changed from PENDING to APPROVED, increase product quantities
            if ($oldStatus == SalesReturn::STATUS_PENDING && $newStatus == SalesReturn::STATUS_APPROVED) {
                foreach ($return->products as $returnProduct) {
                    // Increase product quantity
                    $product = Product::find($returnProduct->product_id);
                    if ($product) {
                        $product->increment('shop_quantity', $returnProduct->quantity);
                        
                        // Record product movement (Sale Return - increases stock)
                        ProductMovement::recordMovement(
                            $returnProduct->product_id,
                            ProductMovement::TYPE_SALE_RETURN,
                            $returnProduct->quantity, // Positive for stock increase
                            'RETURN-' . $return->id . '-APPROVED'
                        );
                    }
                }
            }

            // If status changed from APPROVED back to PENDING or REJECTED, decrease product quantities
            if ($oldStatus == SalesReturn::STATUS_APPROVED && $newStatus != SalesReturn::STATUS_APPROVED) {
                foreach ($return->products as $returnProduct) {
                    // Decrease product quantity (reverse the approval)
                    $product = Product::find($returnProduct->product_id);
                    if ($product) {
                        $product->decrement('shop_quantity', $returnProduct->quantity);
                        
                        // Record product movement (reverse)
                        ProductMovement::recordMovement(
                            $returnProduct->product_id,
                            ProductMovement::TYPE_SALE, // Use SALE type as reversal
                            -$returnProduct->quantity, // Negative for stock decrease
                            'RETURN-' . $return->id . '-REVERSED'
                        );
                    }
                }
            }
        });

        return back()->with('success', 'Return status updated successfully.');
    }

    /**
     * Show return details
     */
    public function show(SalesReturn $return)
    {
        $return->load([
            'products.product',
            'sale',
            'customer',
            'user'
        ]);

        return Inertia::render('Returns/Show', [
            'return' => [
                'id' => $return->id,
                'sale_id' => $return->sale_id,
                'sale_no' => $return->sale?->invoice_no,
                'customer_id' => $return->customer_id,
                'customer_name' => $return->customer?->name,
                'customer_phone' => $return->customer?->phone_number,
                'user_name' => $return->user?->name,
                'return_date' => $return->return_date ? \Carbon\Carbon::parse($return->return_date)->format('Y-m-d') : null,
                'return_date_formatted' => $return->return_date ? \Carbon\Carbon::parse($return->return_date)->format('M d, Y') : 'N/A',
                'status' => $return->status,
                'status_text' => $return->status_text,
                'status_color' => $return->status_color,
                'total_refund' => number_format($return->total_refund, 2),
                'products' => $return->products->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product?->name,
                        'product_barcode' => $item->product?->barcode,
                        'quantity' => $item->quantity,
                        'price' => number_format((float)$item->price, 2),
                        'total' => number_format((float)$item->total, 2),
                        'is_returnable' => $item->product?->return_product == true,
                    ];
                }),
            ]
        ]);
    }

    /**
     * Create a new return (if needed for future implementation)
     */
    public function store(Request $request)
    {
        $request->validate([
            'sale_id' => 'nullable|exists:sales,id',
            'customer_id' => 'nullable|exists:customers,id',
            'return_date' => 'required|date',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.price' => 'required|numeric|min:0',
        ]);

        // Validate that all products are returnable
        foreach ($request->products as $productData) {
            $product = Product::find($productData['product_id']);
            if (!$product->return_product) {
                return back()->withErrors(['products' => "Product {$product->name} is not returnable."]);
            }
        }

        DB::transaction(function () use ($request) {
            $return = SalesReturn::create([
                'sale_id' => $request->sale_id,
                'customer_id' => $request->customer_id,
                'user_id' => Auth::id(),
                'return_date' => $request->return_date,
                'status' => SalesReturn::STATUS_PENDING,
            ]);

            foreach ($request->products as $productData) {
                $total = $productData['quantity'] * $productData['price'];
                
                SalesReturnProduct::create([
                    'sales_return_id' => $return->id,
                    'product_id' => $productData['product_id'],
                    'quantity' => $productData['quantity'],
                    'price' => $productData['price'],
                    'total' => $total,
                ]);
            }
        });

        return redirect()->route('return.index')->with('success', 'Return created successfully.');
    }

    /**
     * Get sales products available for return
     */
    private function getAvailableSalesProducts(Request $request)
    {
        $query = SalesProduct::with([
            'sale' => function($query) {
                $query->select('id', 'invoice_no', 'sale_date', 'customer_id');
            },
            'sale.customer' => function($query) {
                $query->select('id', 'name', 'phone_number');
            },
            'product' => function($query) {
                $query->select('id', 'name', 'barcode', 'return_product');
            }
        ])
        ->whereHas('product', function($query) {
            $query->where('return_product', true);
        })
        ->whereHas('sale', function($query) {
            // Only sales from last 30 days by default
            $query->where('sale_date', '>=', now()->subDays(30));
        });

        // Filter by search if provided
        if ($request->has('sales_search') && $request->sales_search) {
            $search = $request->sales_search;
            $query->where(function($q) use ($search) {
                $q->whereHas('sale', function($query) use ($search) {
                    $query->where('invoice_no', 'like', "%{$search}%");
                })
                ->orWhereHas('sale.customer', function($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                          ->orWhere('phone_number', 'like', "%{$search}%");
                })
                ->orWhereHas('product', function($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                          ->orWhere('barcode', 'like', "%{$search}%");
                });
            });
        }

        // Filter by date range for sales if provided
        if ($request->has('sales_date_from') && $request->sales_date_from) {
            $query->whereHas('sale', function($q) use ($request) {
                $q->whereDate('sale_date', '>=', $request->sales_date_from);
            });
        }
        if ($request->has('sales_date_to') && $request->sales_date_to) {
            $query->whereHas('sale', function($q) use ($request) {
                $q->whereDate('sale_date', '<=', $request->sales_date_to);
            });
        }

        // Exclude products that have already been returned
        $query->whereDoesntHave('returns');

        $salesProducts = $query->orderBy('id', 'desc')
                              ->paginate(10, ['*'], 'sales_page')
                              ->withQueryString();

        return $salesProducts->through(function ($salesProduct) {
            return [
                'id' => $salesProduct->id,
                'sale_id' => $salesProduct->sale_id,
                'sale_no' => $salesProduct->sale?->invoice_no,
                'sale_date' => $salesProduct->sale?->sale_date ? \Carbon\Carbon::parse($salesProduct->sale->sale_date)->format('Y-m-d') : null,
                'sale_date_formatted' => $salesProduct->sale?->sale_date ? \Carbon\Carbon::parse($salesProduct->sale->sale_date)->format('M d, Y') : 'N/A',
                'customer_name' => $salesProduct->sale?->customer?->name ?? 'Walk-in Customer',
                'customer_phone' => $salesProduct->sale?->customer?->phone_number,
                'product_id' => $salesProduct->product_id,
                'product_name' => $salesProduct->product?->name,
                'product_barcode' => $salesProduct->product?->barcode,
                'quantity_sold' => $salesProduct->quantity,
                'price' => $salesProduct->price,
                'total' => $salesProduct->total,
                'is_returnable' => $salesProduct->product?->return_product == true,
                'formatted_price' => number_format((float)$salesProduct->price, 2),
                'formatted_total' => number_format((float)$salesProduct->total, 2),
                'can_return' => true, // Since we're filtering for returnable products
            ];
        });
    }

    /**
     * Create return from selected sales products
     */
    public function createFromSales(Request $request)
    {
        $request->validate([
            'selected_products' => 'required|array|min:1',
            'selected_products.*.sales_product_id' => 'required|exists:sales_products,id',
            'selected_products.*.return_quantity' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request) {
            // Get the first sales product to determine sale and customer
            $firstSalesProduct = SalesProduct::with(['sale', 'product'])->find($request->selected_products[0]['sales_product_id']);
            
            $return = SalesReturn::create([
                'sale_id' => $firstSalesProduct->sale_id,
                'customer_id' => $firstSalesProduct->sale->customer_id,
                'user_id' => Auth::id(),
                'return_date' => now()->format('Y-m-d'),
                'status' => SalesReturn::STATUS_PENDING,
            ]);

            foreach ($request->selected_products as $productData) {
                $salesProduct = SalesProduct::with('product')->find($productData['sales_product_id']);
                
                // Validate return quantity doesn't exceed sold quantity
                if ($productData['return_quantity'] > $salesProduct->quantity) {
                    throw new \Exception("Return quantity cannot exceed sold quantity for {$salesProduct->product->name}");
                }

                // Validate product is returnable
                if (!$salesProduct->product->return_product) {
                    throw new \Exception("Product {$salesProduct->product->name} is not returnable");
                }

                $returnPrice = $salesProduct->price;
                $returnTotal = $productData['return_quantity'] * $returnPrice;
                
                SalesReturnProduct::create([
                    'sales_return_id' => $return->id,
                    'product_id' => $salesProduct->product_id,
                    'quantity' => $productData['return_quantity'],
                    'price' => $returnPrice,
                    'total' => $returnTotal,
                ]);

                // Record product movement (Sale Return - increases stock)
                ProductMovement::recordMovement(
                    $salesProduct->product_id,
                    ProductMovement::TYPE_SALE_RETURN,
                    $productData['return_quantity'], // Positive for stock increase
                    'RETURN-' . $return->id
                );
            }
        });

        return back()->with('success', 'Return created successfully from selected products.');
    }

    /**
     * Update an existing return
     */
    public function update(Request $request, SalesReturn $return)
    {
        // Only allow updates to pending returns
        if ($return->status != SalesReturn::STATUS_PENDING) {
            return back()->withErrors(['error' => 'Only pending returns can be edited.']);
        }

        $request->validate([
            'status' => 'nullable|in:0,1,2',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $return) {
            // If status was APPROVED, reverse the stock movements before updating
            if ($return->status == SalesReturn::STATUS_APPROVED) {
                foreach ($return->products as $oldProduct) {
                    $product = Product::find($oldProduct->product_id);
                    if ($product) {
                        $product->decrement('shop_quantity', $oldProduct->quantity);
                        
                        // Record product movement (reverse)
                        ProductMovement::recordMovement(
                            $oldProduct->product_id,
                            ProductMovement::TYPE_SALE,
                            -$oldProduct->quantity,
                            'RETURN-' . $return->id . '-UPDATED-REVERSED'
                        );
                    }
                }
            }

            // Delete old products
            SalesReturnProduct::where('sales_return_id', $return->id)->delete();

            // Create new products
            foreach ($request->products as $productData) {
                $product = Product::find($productData['product_id']);
                
                // Validate product is returnable
                if (!$product->return_product) {
                    throw new \Exception("Product {$product->name} is not returnable");
                }

                $total = $productData['quantity'] * $productData['price'];
                
                SalesReturnProduct::create([
                    'sales_return_id' => $return->id,
                    'product_id' => $productData['product_id'],
                    'quantity' => $productData['quantity'],
                    'price' => $productData['price'],
                    'total' => $total,
                ]);
            }

            // Update return status if provided
            if ($request->has('status')) {
                $return->update(['status' => $request->status]);

                // If new status is APPROVED, increase stock
                if ($request->status == SalesReturn::STATUS_APPROVED) {
                    foreach ($request->products as $productData) {
                        $product = Product::find($productData['product_id']);
                        if ($product) {
                            $product->increment('shop_quantity', $productData['quantity']);
                            
                            // Record product movement
                            ProductMovement::recordMovement(
                                $productData['product_id'],
                                ProductMovement::TYPE_SALE_RETURN,
                                $productData['quantity'],
                                'RETURN-' . $return->id . '-UPDATED'
                            );
                        }
                    }
                }
            }
        });

        return back()->with('success', 'Return updated successfully.');
    }

    /**
     * Delete a return
     */
    public function destroy(SalesReturn $return)
    {
        // Only allow deletion of pending returns
        if ($return->status != SalesReturn::STATUS_PENDING) {
            return back()->withErrors(['error' => 'Only pending returns can be deleted.']);
        }

        DB::transaction(function () use ($return) {
            // If return was approved, reverse the stock movements
            if ($return->status == SalesReturn::STATUS_APPROVED) {
                foreach ($return->products as $returnProduct) {
                    $product = Product::find($returnProduct->product_id);
                    if ($product) {
                        $product->decrement('shop_quantity', $returnProduct->quantity);
                        
                        // Record product movement (reverse)
                        ProductMovement::recordMovement(
                            $returnProduct->product_id,
                            ProductMovement::TYPE_SALE,
                            -$returnProduct->quantity,
                            'RETURN-' . $return->id . '-DELETED'
                        );
                    }
                }
            }

            // Delete return products (cascading delete)
            SalesReturnProduct::where('sales_return_id', $return->id)->delete();
            
            // Delete the return
            $return->delete();
        });

        return back()->with('success', 'Return deleted successfully.');
    }
}