<?php

declare(strict_types=1);

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Console\Commands\DropTenantDatabasesCommand;
use Illuminate\Database\Seeder;
use Illuminate\Foundation\Console\OptimizeClearCommand;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    /** Seed the application's database. */
    public function run(): void
    {
        Artisan::call('app:horizon:clear');

        Artisan::call(DropTenantDatabasesCommand::class);

        Storage::disk(config('media-library.disk_name'))
            ->deleteDirectory(config('media-library.prefix'));

        $this->call([
            Auth\PermissionSeeder::class,
            Auth\RoleSeeder::class,
            Auth\AdminSeeder::class,
        ]);

        Artisan::call(OptimizeClearCommand::class);
    }
}
