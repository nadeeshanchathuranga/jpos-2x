<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
       Brand::create([
    'id' => 1,
    'name' => 'Default Brand',
    'status' => '2', 
]);
 
         
    }
}
