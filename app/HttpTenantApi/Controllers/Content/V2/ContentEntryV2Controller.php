<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Content\V2;

use App\Features\CMS\CMSBase;
use App\HttpTenantApi\Controllers\BaseCms\BaseCmsController;
use App\HttpTenantApi\Requests\CMS\CreateContentEntryRequest;
use App\HttpTenantApi\Resources\ContentEntryResource;
use Domain\Content\DataTransferObjects\ContentEntryData;
use Domain\Content\Enums\PublishBehavior;
use Domain\Content\Models\Builders\ContentEntryBuilder;
use Domain\Content\Models\Content;
use Domain\Content\Models\ContentEntry;
use Domain\Site\Models\Site;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Domain\Tenant\Support\ApiAbilitties;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[
    Prefix('v2'),
    Middleware(['feature.tenant:'.CMSBase::class, 'auth:sanctum'])
]
class ContentEntryV2Controller extends BaseCmsController
{
    #[Get('/contents/{content}/entries', name: 'v2.contents.entries.index')]
    public function index(Content $content): JsonApiResourceCollection
    {
        $this->checkAbilities(ApiAbilitties::contententry_view->value);

        return ContentEntryResource::collection(
            QueryBuilder::for($content->contentEntries()
                ->where('status', true)
                ->with(['content.blueprint', 'activeRouteUrl', 'blueprintData']))
                ->allowedFilters([
                    'title',
                    'slug',
                    AllowedFilter::exact('locale'),
                    AllowedFilter::callback(
                        'publish_status',
                        fn (ContentEntryBuilder $query, $value) => $query->wherePublishStatus(PublishBehavior::tryFrom($value))
                    ),
                    AllowedFilter::callback(
                        'published_at_start',
                        fn (ContentEntryBuilder $query, $value) => $query->wherePublishedAtRange(publishedAtStart: Carbon::parse($value))
                    ),
                    AllowedFilter::callback(
                        'published_at_end',
                        fn (ContentEntryBuilder $query, $value) => $query->wherePublishedAtRange(publishedAtEnd: Carbon::parse($value))
                    ),
                    AllowedFilter::callback(
                        'published_at_year_month',
                        function (ContentEntryBuilder $query, string|array $value) {
                            $value = Arr::wrap($value);

                            $year = (int) $value[0];
                            $month = filled($value[1] ?? null) ? (int) $value[1] : null;

                            $query->wherePublishedAtYearMonth($year, $month);
                        },
                    ),
                    AllowedFilter::callback(
                        'taxonomies',
                        function (ContentEntryBuilder $query, array $value) {
                            foreach ($value as $taxonomySlug => $taxonomyTermSlugs) {
                                if (filled($taxonomyTermSlugs)) {
                                    $query->whereTaxonomyTerms($taxonomySlug, Arr::wrap($taxonomyTermSlugs));
                                }
                            }
                        }
                    ),
                    AllowedFilter::callback('data', function (Builder $query, string $value) {
                        $query->whereRaw('JSON_SEARCH(data, "all", ?) IS NOT NULL', [$value]);
                    }),
                    AllowedFilter::callback('search_data', function (Builder $query, string $value) {
                        $query->whereRaw('CAST(data AS CHAR) LIKE ?', ['%'.$value.'%']);
                    }),
                    AllowedFilter::exact('sites.id'),
                ])
                ->allowedSorts([
                    'order',
                    'title',
                    'created_at',
                    'updated_at',
                    'published_at',
                ])
                ->allowedIncludes([
                    'taxonomyTerms.taxonomy',
                    'routeUrls',
                    'metaData',
                    'blueprintData.media',
                    'dataTranslation',
                    'parentTranslation',
                ])
                ->jsonPaginate()
        );
    }

    #[Get('/contents/{content}/entries/{contentEntry}', name: 'v2.contents.entries.show')]
    public function show(string $content, string $contentEntry): ContentEntryResource
    {
        $this->checkAbilities(ApiAbilitties::contententry_view->value);

        return ContentEntryResource::make(
            QueryBuilder::for(
                ContentEntry::whereSlug($contentEntry)
                    ->where('status', true)
                    ->whereRelation('content', 'slug', $content)
            )
                ->allowedIncludes([
                    'content',
                    'taxonomyTerms.taxonomy',
                    'routeUrls',
                    'metaData',
                    'blueprintData',
                    'dataTranslation',
                    'parentTranslation',
                ])
                ->firstOrFail()
        );
    }

    #[Post('/contents/{content}/entries/store', name: 'v2.contents.entries.store')]
    public function store(Content $content, CreateContentEntryRequest $request): JsonResponse
    {
        $expectedKeys = [
            'content', 'title', 'route_url', 'status', 'locale', 'sites', 'taxonomy_terms', 'published_at', 'data',
        ];

        $validated = $request->validated();

        $data = collect($expectedKeys)
            ->mapWithKeys(fn ($key) => [$key => $validated[$key] ?? null])
            ->all();

        /** @var array $siteIDs */
        $siteIDs = (array_key_exists('sites', $data) && ! is_null($data['sites'])) ?
           Site::whereIn('domain', explode(',', $data['sites']))->pluck('id')->toArray() :
           [];

        /** @var array $taxonomyIds */
        $taxonomyIds = (array_key_exists('taxonomy_terms', $data) && ! is_null($data['taxonomy_terms'])) ?
            TaxonomyTerm::whereIn('slug', explode(',', $data['taxonomy_terms']))->pluck('id')->toArray() :
             [];

        $publiishedat = is_null($data['published_at']) ? null : Carbon::parse($data['published_at']);

        /** @var \Domain\Admin\Models\Admin $admin */
        $admin = Auth::user();

        $contentEntryData = ContentEntryData::fromArray([
            'title' => $data['title'],
            'locale' => $data['locale'] ?? null,
            'route_url' => [
                'url' => $data['route_url'] ?? '/'.$data['content'].'/'.Str::slug($data['title']),
                'is_override' => ! is_null($data['route_url']),

            ],
            'author_id' => $admin->id,
            'published_at' => $publiishedat,
            'status' => $data['status'] ? true : false,
            'meta_data' => [
                'title' => $data['title'],
                'description' => $data['title'],
            ],
            'data' => $data['data'] ? json_decode($data['data'], true) : [],
            'sites' => $siteIDs,
            'taxonomy_terms' => $taxonomyIds,
        ]);

        try {

            $contentEntry = app(\Domain\Content\Actions\CreateContentEntryAction::class)
                ->execute($content, $contentEntryData);

            return response()->json([
                'message' => 'Content entry created successfully.',
                'entries' => $contentEntry->slug,
            ], 201);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
            ], 422);
        }
    }
}
