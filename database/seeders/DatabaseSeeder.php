<?php

declare(strict_types=1);

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            \Database\Seeders\Auth\PermissionSeeder::class,
            \Database\Seeders\Auth\RoleSeeder::class,
            \Database\Seeders\Auth\AdminSeeder::class,
        ]);
    }
}
