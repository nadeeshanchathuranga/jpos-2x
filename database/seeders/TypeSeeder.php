<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Type;

class TypeSeeder extends Seeder
{
    public function run(): void
    {
        Type::create([
            'id' => 1,
            'name' => 'Default Type',
            'status' => '2',
        ]);
    }
}
