<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tax;

class TaxSeeder extends Seeder
{
    public function run(): void
    {
        Tax::create([
            'id' => 1,
            'name' => 'No Tax',
            'percentage' => 0,
            'type' => '0',
            'status' => '2',
        ]);
    }
}
