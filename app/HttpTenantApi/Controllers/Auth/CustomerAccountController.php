<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Auth;

use App\HttpTenantApi\Resources\CustomerResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;

#[Middleware(['auth:sanctum'])]
class CustomerAccountController
{
    #[Get('account', name: 'account')]
    public function __invoke(Request $request): CustomerResource
    {
        return CustomerResource::make(Auth::user());
    }
}
