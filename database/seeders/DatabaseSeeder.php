<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\BillSetting;
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
        // Check if database already has data
        if (User::count() > 0) {
            $this->command->info('Database already seeded. Skipping...');
            return;
        }

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

              
    }
}
