<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        Customer::create([
            'id' => 1,
            'name' => 'Default Customer',
            'email' => null,
            'phone_number' => null,
            'address' => null,
            'credit_limit' => 0,
            'status' => '2',
        ]);
    }
}
