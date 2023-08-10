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
                    'symbol' => '₱',
                ],
                [
                    'code' => 'USD',
                    'name' => 'US Dollar',
                    'symbol' => '$',
                    'enabled' => true,
                ]
            )->create();

    }
}
