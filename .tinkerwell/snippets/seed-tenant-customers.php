<?php

use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\Tenant\Models\Tenant;
use Domain\Tier\Models\Tier;

tenancy()->initialize(
//    Tenant::where('name', 'xxxxxxxxxxxxxxxxx')->first()
    Tenant::first()
);

CustomerFactory::new()
    ->for(Tier::first())
    ->count(1_000)
    ->create();
