<?php

declare(strict_types=1);

namespace App\HttpApi\Controllers\Page;

use App\Http\Controllers\Controller;
use App\HttpApi\Resources\PageResource;
use Domain\Page\Models\Page;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Prefix;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[Prefix('pages')]
class PageController extends Controller
{
    #[Get('/')]
    public function index(): JsonApiResourceCollection
    {
        return PageResource::collection(
            QueryBuilder::for(Page::class)
                ->allowedFilters(['name', 'slug'])
                ->jsonPaginate()
        );
    }

    #[Get('{page}')]
    public function show(Page $page): PageResource
    {
        return PageResource::make($page);
    }
}
