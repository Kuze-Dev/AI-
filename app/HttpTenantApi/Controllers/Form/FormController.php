<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Form;

use App\Features\CMS\CMSBase;
use App\Http\Controllers\Controller;
use App\HttpTenantApi\Resources\FormResource;
use Domain\Form\Models\Form;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\ApiResource;
use Spatie\RouteAttributes\Attributes\Middleware;
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
                ->allowedFilters(['name'])
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
