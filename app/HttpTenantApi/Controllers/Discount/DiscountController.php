<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Discount;

use App\Features\ECommerce\ECommerceBase;
use App\Http\Controllers\Controller;
use App\HttpTenantApi\Resources\DiscountResource;
use Domain\Discount\Enums\DiscountStatus;
use Domain\Discount\Models\Discount;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\ApiResource;
use Spatie\RouteAttributes\Attributes\Middleware;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[
    ApiResource('discounts', only: ['index', 'show']),
    Middleware('feature.tenant:' . ECommerceBase::class)
]
class DiscountController extends Controller
{
    public function index(): JsonApiResourceCollection
    {
        return DiscountResource::collection(
            QueryBuilder::for(Discount::query()
                ->whereStatus(DiscountStatus::ACTIVE)
                ->jsonPaginate())
                ->allowedIncludes([
                    'discountCondition',
                    'discountRequirement'])
        );
    }

    public function show(string $code): DiscountResource
    {
        return DiscountResource::make(
            QueryBuilder::for(Discount::whereCode($code)
                ->whereStatus(DiscountStatus::ACTIVE)
                ->where(function ($query) {
                    $query->where('max_uses', '>', 0)
                        ->orWhereNull('max_uses');
                })
                ->where(function ($query) {
                    $query->where('valid_end_at', '>=', now())
                        ->orWhereNull('valid_end_at');
                }))
                ->allowedIncludes(['discountCondition', 'discountRequirement'])
                ->firstOrFail()
        );
    }
}
