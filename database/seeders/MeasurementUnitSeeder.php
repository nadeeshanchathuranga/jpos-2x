<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MeasurementUnit;

class MeasurementUnitSeeder extends Seeder
{
    public function run(): void
    {
        MeasurementUnit::create([
            'id' => 1,
            'name' => 'Default Unit',
            'symbol' => 'N/A',
            'status' => '2',
        ]);
    }
}
