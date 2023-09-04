<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Domain\Internationalization\Database\Factories\LocaleFactory;

class LocaleSeeder extends Seeder
{
    public function run(): void
    {
        LocaleFactory::new()->create([
            'name' => 'English (en)',
            'code' => 'en',
            'is_default' => true,
        ]);
    }
}
