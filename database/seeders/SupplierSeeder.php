<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        Supplier::create([
            'id' => 1,
            'name' => 'Default Supplier',
            'email' => null,
            'phone' => null,
            'address' => null, 
            'status' => '2',
        ]);
    }
}
