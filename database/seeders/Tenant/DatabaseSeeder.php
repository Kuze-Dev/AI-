<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant;

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

        if ( ! app()->runningUnitTests()) {
            $this->call([
                Address\CountrySeeder::class,
                Customer\CustomerSeeder::class,
            ]);
        }
    }
}
