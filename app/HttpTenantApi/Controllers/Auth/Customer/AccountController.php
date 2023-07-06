<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Auth\Customer;

use App\Features\ECommerce\ECommerceBase;
use App\HttpTenantApi\Resources\CustomerResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;

#[Middleware(['auth:sanctum', 'feature.tenant:' . ECommerceBase::class])]
class AccountController
{
    #[Get('account', name: 'account')]
    public function __invoke(Request $request): CustomerResource
    {
        return CustomerResource::make(Auth::user());
    }
}
