<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Search;

use App\Http\Middleware\TenantApiAuthorizationMiddleware;
use App\HttpTenantApi\Controllers\BaseCms\BaseCmsController;
use App\HttpTenantApi\Resources\ContentEntryResource;
use App\HttpTenantApi\Resources\PageResource;
use BadMethodCallException;
use Domain\Content\Models\ContentEntry;
use Domain\Page\Enums\Visibility;
use Domain\Page\Models\Page;
use Domain\Tenant\Support\ApiAbilitties;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\RouteAttributes\Attributes\Get;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

class SearchController extends BaseCmsController
{
    private const array SEARCHABLE_MODELS = ['page', 'contentEntry'];

    #[Get('/search', middleware: TenantApiAuthorizationMiddleware::class)]
    public function index(Request $request): JsonResponse
    {
        $this->checkAbilities(ApiAbilitties::cms_search->value);

        $validated = $request->validate([
            'query' => 'required|string',
            'filter' => 'nullable|array',
        ]);

        $results = array_reduce(
            ($validated['filter']['models'] ?? null)
                ? explode(',', (string) $validated['filter']['models'])
                : self::SEARCHABLE_MODELS,
            function (array $results, string $model) use ($request, $validated) {

                $search = match ($model) {
                    'page' => $this->getPageResults(
                        $validated['query'], $validated['filter'] ?? null
                    ),
                    'contentEntry' => $this->getContentEntryResults(
                        $validated['query'], $validated['filter'] ?? null
                    ),
                    default => throw new BadMethodCallException(
                        "Searching for `{$model}` may be invalid or not yet implemented."
                    ),
                };

                return array_merge(
                    $results,
                    (array) $search->toArray($request)
                );
            },
            []
        );

        return response()->json(['data' => $results]);
    }

    /** @return JsonApiResourceCollection <int, Page> */
    protected function getPageResults(string $searchQuery, ?array $filter): JsonApiResourceCollection
    {
        return PageResource::collection(
            Page::query()
                ->where('name', 'LIKE', "%{$searchQuery}%")
                ->whereNotNull('published_at')
                ->when(
                    $filter['sites.id'] ?? null,
                    fn ($query, $siteIds) => $query->wherehas('sites', fn ($q) => $q->whereIn('site_id', explode(',', (string) $siteIds)))
                )
                ->limit(20)
                ->get()
        );
    }

    protected function getContentEntryResults(string $searchQuery, ?array $filter): JsonApiResourceCollection
    {
        return ContentEntryResource::collection(
            ContentEntry::query()
                ->with('blueprintData')
                ->where('title', 'LIKE', "%{$searchQuery}%")
                ->where('status', true)
                ->whereRelation('content', 'visibility', '!=', Visibility::AUTHENTICATED->value)
                ->when(
                    $filter['content_ids'] ?? null,
                    fn ($query, $contentIds) => $query->whereIn('content_id', explode(',', (string) $contentIds))
                )
                ->when(
                    $filter['sites.id'] ?? null,
                    fn ($query, $siteIds) => $query->wherehas('sites', fn ($q) => $q->whereIn('site_id', explode(',', (string) $siteIds)))
                )
                ->limit(20)
                ->get()
        );
    }
}
