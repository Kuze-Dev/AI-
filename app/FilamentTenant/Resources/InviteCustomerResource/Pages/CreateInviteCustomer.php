<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\InviteCustomerResource\Pages;

use App\FilamentTenant\Resources\CustomerResource;
use App\FilamentTenant\Resources\InviteCustomerResource;

class CreateInviteCustomer extends CustomerResource\Pages\CreateCustomer
{
    protected static string $resource = InviteCustomerResource::class;
}
