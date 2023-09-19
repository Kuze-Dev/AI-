<?php

namespace Database\Seeders\Tenant\Service;

use Domain\Service\Databases\Factories\ServiceFactory;
use Illuminate\Database\Seeder;
use Support\MetaData\Database\Factories\MetaDataFactory;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ServiceFactory::new([

        ])
        ->has(MetaDataFactory::new())
        ->create();
    }
}
