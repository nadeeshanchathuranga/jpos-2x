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

        User::create([
            'name' => 'Admin User',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('123456789'),
            'role' => 0,
        ]);

        // Ensure a BillSetting exists with provided company details
        $bill = BillSetting::first();
            $data = [
                'logo_path' => 'bill_logos/logo_6940f7eb1b37f.png',
                'company_name' => 'jaan network',
                'address' => 'No:55/b, colombo 03',
                'mobile_1' => '0717598064',
                'mobile_2' => '0717598064',
                'email' => 'nadishan@gmail.com',
                'website_url' => 'https://www.hirunews.lk/',
                'footer_description' => 'විශේෂ කාර්යබලකායේ නිළධාරින් විසින් සැකකරු අත්අඩංගුවට ගත්තා.',
                'print_size' => '80mm',
            ];
        
            if ($bill) {
                $bill->update($data);
            } else {
                BillSetting::create($data);
            }
    }
}
