<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Form;

use Domain\Form\Models\Form;
use App\Http\Controllers\Controller;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\HttpTenantApi\Resources\FormResource;
use Spatie\RouteAttributes\Attributes\ApiResource;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[ApiResource('forms', only: ['index', 'show'])]
class FormController extends Controller
{
    public function index(): JsonApiResourceCollection
    {
        return FormResource::collection(
            QueryBuilder::for(Form::query()->select(['blueprint_id', 'name',  'slug']))
                ->allowedIncludes('blueprint')
                ->allowedFilters(['name', AllowedFilter::exact('sites.id')])
                ->jsonPaginate()
        );
    }

    public function show(Form $form): FormResource
    {
        return FormResource::make($form);
    }
}
