<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;
use App\Models\MeasurementUnit;

echo "=== UNIT CONVERSION CHECK ===\n\n";

// Check measurement units
echo "Available Measurement Units:\n";
echo str_repeat("-", 80) . "\n";
$units = MeasurementUnit::all();
if ($units->count() > 0) {
    foreach ($units as $unit) {
        echo sprintf("ID: %d | Name: %s | Symbol: %s | Status: %s\n", 
            $unit->id, 
            $unit->name,
            $unit->symbol ?? 'N/A',
            $unit->status ? 'Active' : 'Inactive'
        );
    }
} else {
    echo "No measurement units found.\n";
}

echo "\n";

// Check products with their units and conversion rates
echo "Products with Unit Configuration:\n";
echo str_repeat("-", 80) . "\n";
$products = Product::with(['purchaseUnit', 'transferUnit', 'salesUnit'])->take(10)->get();

foreach ($products as $product) {
    echo sprintf("\nProduct ID: %d | %s\n", $product->id, $product->name);
    echo "  Purchase Unit: " . ($product->purchaseUnit->name ?? 'None') . " (ID: " . ($product->purchase_unit_id ?? 'N/A') . ")\n";
    echo "  Transfer Unit: " . ($product->transferUnit->name ?? 'None') . " (ID: " . ($product->transfer_unit_id ?? 'N/A') . ")\n";
    echo "  Sales Unit: " . ($product->salesUnit->name ?? 'None') . " (ID: " . ($product->sales_unit_id ?? 'N/A') . ")\n";
    echo sprintf("  Conversion Rates:\n");
    echo sprintf("    Purchase → Transfer: %.4f (purchase_to_transfer_rate)\n", $product->purchase_to_transfer_rate ?? 0);
    echo sprintf("    Transfer → Sales: %.4f (transfer_to_sales_rate)\n", $product->transfer_to_sales_rate ?? 0);
    echo sprintf("  Current Stock:\n");
    echo sprintf("    Store: %.2f | Shop: %.2f\n", $product->store_quantity, $product->shop_quantity);
    
    // Calculate what happens if you transfer 1 purchase unit
    $purchaseToTransfer = $product->purchase_to_transfer_rate > 0 ? $product->purchase_to_transfer_rate : 1.0;
    $transferToSales = $product->transfer_to_sales_rate > 0 ? $product->transfer_to_sales_rate : 1.0;
    $finalQty = 1 * $purchaseToTransfer * $transferToSales;
    
    echo sprintf("  Example: Transfer 1 %s →\n", $product->purchaseUnit->name ?? 'unit');
    echo sprintf("    = 1 × %.4f × %.4f = %.4f %s (final qty in shop)\n", 
        $purchaseToTransfer, 
        $transferToSales, 
        $finalQty,
        $product->salesUnit->name ?? 'units'
    );
}

echo "\n=== END OF CHECK ===\n";
