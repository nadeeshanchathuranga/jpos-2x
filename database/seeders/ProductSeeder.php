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
                'name' => 'Rice 1kg',
                'barcode' => 'PRD' . str_pad(1, 8, '0', STR_PAD_LEFT),
                'brand_id' => 1,
                'category_id' => 1,
                'type_id' => 1,
                'measurement_unit_id' => 1,
                'discount_id' => null,
                'tax_id' => 1,
                'qty' => 1,
                'purchase_price' => 5.00,
                'wholesale_price' => 10.00,
                'retail_price' => 8.00,
                'return_product' => true,
                'purchase_unit_id' => 1,
                'sales_unit_id' => 1,
                'transfer_unit_id' => 1,
                'purchase_to_transfer_rate' => 1.00,
                'purchase_to_sales_rate' => 1.00,
                'transfer_to_sales_rate' => 1.00,
                'status' => 2,
                'image' => null,
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
