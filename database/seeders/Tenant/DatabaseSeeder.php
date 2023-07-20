<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant;

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
            Tier\TierSeeder::class,
            Customer\CustomerSeeder::class,
        ]);
    }
}
