<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant\Cart;

use Domain\Address\Models\Address;
use Domain\Customer\Models\Customer;
use Domain\Tier\Models\Tier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Tier::updateOrCreate(
            ['name' => 'Group 1'],
            ['description' => 'Group 1 description']
        );

        Customer::updateOrCreate(
            ['cuid' => 'abcd1234'],
            [
                'tier_id' => 1,
                'email' => 'benedict.halcyondigital@gmail.com',
                'password' => bcrypt('password'),
                'first_name' => 'Jb',
                'last_name' => 'Regore',
                'mobile' => '09208024445',
                'status' => 'active',
                'birth_date' => '01/02/2001',
            ]
        );

        Address::updateOrCreate(
            ['customer_id' => 1, 'is_default_shipping' => 1],
            [
                'state_id' => 1,
                'label_as' => 'office',
                'address_line_1' => 'L3-02B, SOHO Retail Podium',
                'zip_code' => '1550',
                'city' => 'Mandaluyong',
                'is_default_shipping' => 1,
                'is_default_billing' => 0,
            ]
        );

        Address::updateOrCreate(
            ['customer_id' => 1, 'is_default_billing' => 1],
            [
                'state_id' => 1,
                'label_as' => 'home',
                'address_line_1' => '855 Proper San Jose',
                'zip_code' => '2014',
                'city' => 'Pampanga',
                'is_default_shipping' => 0,
                'is_default_billing' => 1,
            ]
        );
    }
}
