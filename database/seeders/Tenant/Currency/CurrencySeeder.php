<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant\Currency;

use Domain\Currency\Database\Factories\CurrencyFactory;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        CurrencyFactory::new()
            ->count(2)
            ->sequence(
                [
                    'code' => 'PHP',
                    'name' => 'Philippine Peso',
                    'exchange_rate' => 56.00,
                    'default' => false,
                ],
                [
                    'code' => 'USD',
                    'name' => 'US Dollar',
                    'exchange_rate' => 1.00,
                    'default' => true,
                ]
            )
            ->create(['enabled' => false]);

    }
}
