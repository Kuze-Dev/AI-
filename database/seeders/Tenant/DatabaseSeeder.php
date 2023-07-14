<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant;

use Domain\Discount\Database\Seeders\DiscountSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Artisan::call('permission:cache-reset');

        $this->call([
            Auth\PermissionSeeder::class,
            Auth\RoleSeeder::class,
            Auth\AdminSeeder::class,
            Page\PageSeeder::class,
            DiscountSeeder::class,
            Product\ProductSeeder::class,
            Address\CountrySeeder::class,
            Currency\CurrencySeeder::class,
            Tier\TierSeeder::class,
            Customer\CustomerSeeder::class,
        ]);
    }
}
