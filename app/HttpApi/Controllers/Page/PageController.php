<?php

declare(strict_types=1);

namespace App\HttpApi\Controllers\Page;

use App\Http\Controllers\Controller;
use App\HttpApi\Resources\PageResource;
use Domain\Page\Enums\PageBehavior;
use Domain\Page\Models\Page;
use Illuminate\Database\Eloquent\Builder;
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
            QueryBuilder::for(
                Page::where(
                    fn (Builder $query) => $query
                        ->where('published_at', '>', now())
                        ->where('future_behavior', PageBehavior::PUBLIC)
                )
                    ->orWhere(
                        fn (Builder $query) => $query
                            ->where('published_at', '<=', now())
                            ->where('past_behavior', PageBehavior::PUBLIC)
                    )
            )
                ->jsonPaginate()
        );
    }

    #[Get('{page}')]
    public function show(Page $page): PageResource
    {
        if ($page->published_at > now() && $page->future_behavior === PageBehavior::HIDDEN) {
            abort(404);
        }

        if ($page->published_at < now() && $page->past_behavior === PageBehavior::HIDDEN) {
            abort(404);
        }

        return PageResource::make($page);
    }
}
