<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MeasurementUnit;

class MeasurementUnitSeeder extends Seeder
{
    public function run(): void
    {
        MeasurementUnit::insert([
    [
        'id' => 1,
        'name' => 'Unit',
        'symbol' => 'UOM',
        'status' => '2',
    ],
    [
        'id' => 2,
        'name' => 'Kilogram',
        'symbol' => 'kg',
        'status' => '1',
    ],
    [
        'id' => 3,
        'name' => 'Gram',
        'symbol' => 'g',
        'status' => '1',
    ],
    [
        'id' => 4,
        'name' => 'Liter',
        'symbol' => 'L',
        'status' => '1',
    ],
    [
        'id' => 5,
        'name' => 'Milliliter',
        'symbol' => 'mL',
        'status' => '1',
    ],
    [
        'id' => 6,
        'name' => 'Meter',
        'symbol' => 'm',
        'status' => '1',
    ],
    [
        'id' => 7,
        'name' => 'Centimeter',
        'symbol' => 'cm',
        'status' => '1',
    ],
    [
        'id' => 8,
        'name' => 'Piece',
        'symbol' => 'pc',
        'status' => '1',
    ],
    [
        'id' => 9,
        'name' => 'Box',
        'symbol' => 'box',
        'status' => '1',
    ],
    [
        'id' => 10,
        'name' => 'Dozen',
        'symbol' => 'dz',
        'status' => '1',
    ],
]);

    }
}
