<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant;

use Domain\Discount\Database\Seeders\DiscountSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Foundation\Console\OptimizeClearCommand;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Artisan::call(OptimizeClearCommand::class);

        $this->call([
            Auth\PermissionSeeder::class,
            Auth\RoleSeeder::class,
            Auth\AdminSeeder::class,
            Page\PageSeeder::class,
            Address\CountrySeeder::class,
            DiscountSeeder::class,
            Product\ProductSeeder::class,
            Currency\CurrencySeeder::class,
            Tier\TierSeeder::class,
            Customer\CustomerSeeder::class,
            ShippingMethod\ShippingMethodSeeder::class,
        ]);
    }
}
