<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Search;

use App\HttpTenantApi\Resources\ContentEntryResource;
use App\HttpTenantApi\Resources\PageResource;
use BadMethodCallException;
use Domain\Content\Models\ContentEntry;
use Domain\Page\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\RouteAttributes\Attributes\Get;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

class SearchController
{
    private const SEARCHABLE_MODELS = ['page', 'contentEntry'];

    #[Get('/search')]
    public function index(Request $request): JsonResponse
    {
        $results = array_reduce(
            ($request->filter['models'] ?? null)
                ? explode(',', $request->filter['models'])
                : self::SEARCHABLE_MODELS,
            function (array $results, string $model) use ($request) {
                if (! method_exists($this, $method = 'get'.Str::of($model)->studly().'Results')) {
                    throw new BadMethodCallException("Searching for `{$model}` may be invalid or not yet implemented.");
                }

                return array_merge(
                    $results,
                    $this->{$method}($request->get('query'), $request->filter)->toArray($request)
                );
            },
            []
        );

        return response()->json(['data' => $results]);
    }

    /** @return JsonApiResourceCollection <int, Page> */
    protected function getPageResults(string $searchQuery, array $filter = null): JsonApiResourceCollection
    {
        return PageResource::collection(
            Page::query()
                ->where('name', 'LIKE', "%{$searchQuery}%")
                ->limit(20)
                ->get()
        );
    }

    protected function getContentEntryResults(string $searchQuery, array $filter = null): JsonApiResourceCollection
    {
        return ContentEntryResource::collection(
            ContentEntry::query()
                ->where('title', 'LIKE', "%{$searchQuery}%")
                ->when(
                    $filter['content_ids'] ?? null,
                    fn ($query, $contentIds) => $query->whereIn('content_id', explode(',', $contentIds))
                )
                ->limit(20)
                ->get()
        );
    }
}
