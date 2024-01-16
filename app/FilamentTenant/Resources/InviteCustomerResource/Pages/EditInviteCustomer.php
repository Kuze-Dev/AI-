<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\InviteCustomerResource\Pages;

use App\FilamentTenant\Resources\CustomerResource;
use App\FilamentTenant\Resources\InviteCustomerResource;

class EditInviteCustomer extends CustomerResource\Pages\EditCustomer
{
    protected static string $resource = InviteCustomerResource::class;
}
