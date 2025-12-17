<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call([   
            BrandSeeder::class,
            CategorySeeder::class,
            TypeSeeder::class,
            MeasurementUnitSeeder::class,
            SupplierSeeder::class,
            CustomerSeeder::class,
            DiscountSeeder::class,
            TaxSeeder::class,
            ProductSeeder::class,
        ]);

          // Admin User - Full Access
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('123456789'),
            'role' => 0, 
        ]);

        // Manager - All access except settings and setting reports
        User::create([
            'name' => 'Manager User',
            'email' => 'manager@gmail.com',
            'password' => Hash::make('123456789'),
            'role' => 1, 
        ]);

        // Cashier - Only sales and sales reports
        User::create([
            'name' => 'Cashier User',
            'email' => 'cashier@gmail.com',
            'password' => Hash::make('123456789'),
            'role' => 2, 
        ]);

        // Stock Keeper - Inventory management
        User::create([
            'name' => 'Stock Keeper',
            'email' => 'stockkeeper@gmail.com',
            'password' => Hash::make('123456789'),
            'role' => 3, 
        ]);

              
    }
}
