<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\StockTransferReturn;
use App\Models\Product;
use App\Models\User;

echo "=== STOCK TRANSFER RETURN SYSTEM CHECK ===\n\n";

// Check table exists
echo "Checking database table...\n";
try {
    $count = StockTransferReturn::count();
    echo "✓ Table exists. Current returns: $count\n\n";
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
    exit;
}

// Sample product stock levels
echo "Sample Products (Shop Stock Available for Return):\n";
echo str_repeat("-", 80) . "\n";
$products = Product::take(5)->get(['id', 'name', 'shop_quantity', 'store_quantity']);
foreach ($products as $p) {
    echo sprintf("ID: %d | %s\n", $p->id, $p->name);
    echo sprintf("  Shop: %.2f (can return) | Store: %.2f\n\n", 
        $p->shop_quantity, 
        $p->store_quantity
    );
}

// Recent returns
echo "Recent Stock Transfer Returns:\n";
echo str_repeat("-", 80) . "\n";
$returns = StockTransferReturn::with(['product', 'user'])->latest()->take(5)->get();
if ($returns->count() > 0) {
    foreach ($returns as $r) {
        echo sprintf("Return #%s | Date: %s | Status: %s\n", 
            $r->return_no, 
            $r->return_date,
            $r->status
        );
        echo sprintf("  Product: %s | Qty: %.2f | User: %s\n", 
            $r->product->name ?? 'N/A',
            $r->stock_transfer_quantity,
            $r->user->name ?? 'N/A'
        );
        echo sprintf("  Reason: %s\n\n", $r->reason ?? 'N/A');
    }
} else {
    echo "No returns found yet.\n";
}

echo "\n=== SYSTEM READY ===\n";
echo "Access at: http://localhost:8000/stock-transfer-returns\n";
