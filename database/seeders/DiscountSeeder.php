<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Discount;

class DiscountSeeder extends Seeder
{
    public function run(): void
    {
        Discount::create([
            'id' => 1,
            'name' => 'No Discount',
            'type' => '0',
            'value' => 0,
            'start_date' => null,
            'end_date' => null,
            'status' => '2',
        ]);
    }
}
