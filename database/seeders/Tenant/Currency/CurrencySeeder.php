<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant\Currency;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('currencies')->insert([
            [
                'code' => 'PHP',
                'name' => 'Philippine Peso',
                'enabled' => false,
                'exchange_rate' => 56.00,
                'default' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'USD',
                'name' => 'US Dollar',
                'enabled' => false,
                'exchange_rate' => 1.00,
                'default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
