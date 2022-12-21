<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Form;

use App\Http\Controllers\Controller;
use App\HttpTenantApi\Resources\FormResource;
use Domain\Form\Models\Form;
use Spatie\QueryBuilder\QueryBuilder;
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
                ->allowedFilters(['name'])
                ->jsonPaginate()
        );
    }

    public function show(Form $form): FormResource
    {
        return FormResource::make($form);
    }
}
