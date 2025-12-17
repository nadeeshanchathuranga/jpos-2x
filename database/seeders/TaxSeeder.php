<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tax;

class TaxSeeder extends Seeder
{
    public function run(): void
    {
       Tax::insert([
    [
        'id' => 1,
        'name' => 'No Tax',
        'percentage' => 0,
        'type' => '0',
        'status' => '2',
    ],
    [
        'id' => 2,
        'name' => 'VAT 5%',
        'percentage' => 5,
        'type' => '1',
        'status' => '1',
    ],
    [
        'id' => 3,
        'name' => 'VAT 10%',
        'percentage' => 10,
        'type' => '1',
        'status' => '1',
    ],
    [
        'id' => 4,
        'name' => 'GST 12%',
        'percentage' => 12,
        'type' => '1',
        'status' => '1',
    ],
    [
        'id' => 5,
        'name' => 'GST 18%',
        'percentage' => 18,
        'type' => '1',
        'status' => '1',
    ],
    [
        'id' => 6,
        'name' => 'Service Tax 15%',
        'percentage' => 15,
        'type' => '1',
        'status' => '1',
    ],
    [
        'id' => 7,
        'name' => 'Luxury Tax 20%',
        'percentage' => 20,
        'type' => '1',
        'status' => '1',
    ],
    [
        'id' => 8,
        'name' => 'Import Duty 8%',
        'percentage' => 8,
        'type' => '1',
        'status' => '1',
    ],
    [
        'id' => 9,
        'name' => 'Environmental Tax 2%',
        'percentage' => 2,
        'type' => '1',
        'status' => '1',
    ],
    [
        'id' => 10,
        'name' => 'Local Sales Tax 7%',
        'percentage' => 7,
        'type' => '1',
        'status' => '1',
    ],
]);

    }
}
