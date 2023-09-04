<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Form;

use Domain\Form\Models\Form;
use App\Features\CMS\CMSBase;
use App\Http\Controllers\Controller;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\HttpTenantApi\Resources\FormResource;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\ApiResource;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[
    ApiResource('forms', only: ['index', 'show']),
    Middleware('feature.tenant:' . CMSBase::class)
]
class FormController extends Controller
{
    public function index(): JsonApiResourceCollection
    {
        return FormResource::collection(
            QueryBuilder::for(Form::query())
                ->allowedIncludes('blueprint')
                ->allowedFilters(['name', AllowedFilter::exact('locale'), AllowedFilter::exact('sites.id')])
                ->jsonPaginate()
        );
    }

    public function show(string $form): FormResource
    {
        return FormResource::make(
            QueryBuilder::for(Form::whereSlug($form))
                ->allowedIncludes('blueprint')
                ->firstOrFail()
        );
    }
}
