<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Form;

use App\Features\CMS\CMSBase;
use App\Http\Middleware\TenantApiAuthorizationMiddleware;
use App\HttpTenantApi\Controllers\BaseCms\BaseCmsController;
use App\HttpTenantApi\Resources\FormResource;
use Domain\Form\Models\Form;
use Domain\Tenant\Support\ApiAbilitties;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\ApiResource;
use Spatie\RouteAttributes\Attributes\Middleware;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[
    ApiResource('forms', only: ['index', 'show']),
    Middleware(['feature.tenant:'.CMSBase::class, TenantApiAuthorizationMiddleware::class])
]
class FormController extends BaseCmsController
{
    public function index(): JsonApiResourceCollection
    {
        $this->checkAbilities(ApiAbilitties::form_view->value);

        return FormResource::collection(
            QueryBuilder::for(Form::query())
                ->allowedIncludes('blueprint')
                ->allowedFilters(['name', AllowedFilter::exact('locale'), AllowedFilter::exact('sites.id')])
                ->jsonPaginate()
        );
    }

    public function show(string $form): FormResource
    {
        $this->checkAbilities(ApiAbilitties::form_view->value);

        return FormResource::make(
            QueryBuilder::for(Form::whereSlug($form))
                ->allowedIncludes('blueprint')
                ->firstOrFail()
        );
    }
}
