<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Form;

use App\Features\ECommerce\ECommerceBase;
use App\Http\Controllers\Controller;
use App\HttpTenantApi\Resources\FormResource;
use Domain\Form\Models\Form;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\ApiResource;
use Spatie\RouteAttributes\Attributes\Middleware;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[
    ApiResource('discounts', only: ['add', 'remove']),
    Middleware('feature.tenant:' . ECommerceBase::class)
]
class CartDiscountController extends Controller
{
    public function add(Request $request, string $cartID): JsonApiResourceCollection
    {
        return FormResource::collection(
            QueryBuilder::for(Form::query())
                ->allowedIncludes('blueprint')
                ->allowedFilters(['name'])
                ->jsonPaginate()
        );
    }

    public function remove(string $form): FormResource
    {
        return FormResource::make(
            QueryBuilder::for(Form::whereSlug($form))
                ->allowedIncludes('blueprint')
                ->firstOrFail()
        );
    }
}
