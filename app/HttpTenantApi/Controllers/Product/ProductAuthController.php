<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Product;

use Spatie\RouteAttributes\Attributes\ApiResource;
use Spatie\RouteAttributes\Attributes\Middleware;

#[
    ApiResource('auth/products', only: ['index', 'show'], names: 'auth.products'),
    Middleware(['auth:sanctum'])
]
class ProductAuthController extends ProductController {}
