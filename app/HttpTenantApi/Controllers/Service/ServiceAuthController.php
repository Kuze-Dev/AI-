<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Service;

use Spatie\RouteAttributes\Attributes\ApiResource;
use Spatie\RouteAttributes\Attributes\Middleware;

#[
    ApiResource('auth/services', only: ['index', 'show'], names: 'auth.services'),
    Middleware(['auth:sanctum'])
]
class ServiceAuthController extends ServiceController {}
