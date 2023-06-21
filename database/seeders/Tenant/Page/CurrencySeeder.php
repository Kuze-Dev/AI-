<?php

namespace Database\Seeders\Tenant\Page;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('currencies')->insert([
            [
                'code' => 'PHP',
                'name' => 'Philippine Peso',
                'enabled' => true,
                'exchange_rate' => 56.00,
                'default' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'USD',
                'name' => 'US Dollar',
                'enabled' => true,
                'exchange_rate' => 1.00,
                'default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
