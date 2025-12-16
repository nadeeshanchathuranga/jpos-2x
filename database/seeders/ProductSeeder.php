<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
            $products = [
        [
            'name' => 'Default Product',
            'barcode' => 'PRD' . str_pad(1, 8, '0', STR_PAD_LEFT),
            'brand_id' => 1,
            'category_id' => 1,
            'type_id' => 1,
            'discount_id' => 1,
            'tax_id' => 1,
            'shop_quantity' => 1,
            'shop_low_stock_margin' => 0, 
            'store_quantity' => 0,
            'store_low_stock_margin' => 0, 
            'purchase_price' => 0.00,
            'wholesale_price' => 0.00,
            'retail_price' => 0.00,
            'return_product' => true,
            'purchase_unit_id' => 1,
            'sales_unit_id' => 1,
            'transfer_unit_id' => 1,
            'purchase_to_transfer_rate' => 0.00,
            'transfer_to_sales_rate' => 0.00,
            'status' => 2,
            'image' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ];


        foreach ($products as $product) {
            Product::updateOrCreate(
                ['barcode' => $product['barcode']],
                $product
            );
        }
    }
}
