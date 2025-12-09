<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ProductMovement;
use App\Models\Product;
use App\Models\ProductTransferRequest;
use App\Models\ProductReleaseNote;

echo "=== PRODUCT TRANSFER REQUEST CHECK ===\n\n";

// Check recent Product Transfer Requests
echo "Recent Product Transfer Requests:\n";
echo str_repeat("-", 80) . "\n";
$ptrs = ProductTransferRequest::latest()->take(5)->get();
foreach ($ptrs as $ptr) {
    echo sprintf("PTR #%s | Date: %s | Status: %s | User: %d\n", 
        $ptr->transfer_no, 
        $ptr->request_date, 
        $ptr->status ?? 'N/A',
        $ptr->user_id
    );
}

echo "\n";

// Check recent Product Release Notes
echo "Recent Product Release Notes (PRN):\n";
echo str_repeat("-", 80) . "\n";
$prns = ProductReleaseNote::latest()->take(5)->get();
foreach ($prns as $prn) {
    echo sprintf("PRN #%s | Date: %s | PTR: %s\n", 
        $prn->purchase_request_note_no, 
        $prn->purchase_request_note_date,
        $prn->transfer_no ?? 'N/A'
    );
}

echo "\n";

// Check recent Product Movements (TYPE_PURCHASE_RETURN = 2 means storage → shop)
echo "Recent Product Movements (Storage → Shop Transfers):\n";
echo str_repeat("-", 80) . "\n";
$movements = ProductMovement::where('movement_type', 2)->latest()->take(5)->get();
if ($movements->count() > 0) {
    foreach ($movements as $m) {
        $product = Product::find($m->product_id);
        echo sprintf("ID: %d | Product: %s (ID:%d) | Qty: %.2f | Date: %s\n", 
            $m->id, 
            $product->name ?? 'Unknown',
            $m->product_id, 
            $m->quantity, 
            $m->created_at
        );
    }
} else {
    echo "No transfer movements found yet.\n";
}

echo "\n";

// Sample product stock levels
echo "Sample Product Stock Levels:\n";
echo str_repeat("-", 80) . "\n";
$products = Product::take(5)->get(['id', 'name', 'store_quantity', 'shop_quantity', 'purchase_to_transfer_rate', 'transfer_to_sales_rate']);
foreach ($products as $p) {
    echo sprintf("ID: %d | %s\n", $p->id, $p->name);
    echo sprintf("  Store: %.2f | Shop: %.2f | Rates: %.2f × %.2f\n\n", 
        $p->store_quantity, 
        $p->shop_quantity,
        $p->purchase_to_transfer_rate ?? 1.0,
        $p->transfer_to_sales_rate ?? 1.0
    );
}

echo "\n=== END OF CHECK ===\n";
