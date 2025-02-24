<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant;

use Database\Seeders\Tenant\Product\ImageProductUploaderSeeder;
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
            LocaleSeeder::class,
            Page\PageSeeder::class,
            Tier\TierSeeder::class,
        ]);
        
        if (! app()->runningUnitTests()) {
            $this->call([
                Address\CountrySeeder::class,
                DiscountSeeder::class,
                Product\ProductSeeder::class,
                // ImageProductUploaderSeeder::class,
                Currency\CurrencySeeder::class,
                Customer\CustomerSeeder::class,
                // ShippingMethod\ShippingMethodSeeder::class,
                ShippingMethod\ShippingBoxSeeder::class,
            ]);
        }
    }
}
