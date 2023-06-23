<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Search;

use App\HttpTenantApi\Resources\ContentEntryResource;
use App\HttpTenantApi\Resources\PageResource;
use Domain\Content\Models\ContentEntry;
use Domain\Page\Models\Page;
use Illuminate\Http\JsonResponse;
use Spatie\RouteAttributes\Attributes\Get;
use InvalidArgumentException;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

class SearchController
{
    #[
        Get('/search')
    ]
    public function index(Request $request): JsonResponse
    {
        $searchable_models = ['page', 'contentEntry'];
        $method = '';
        $filter = request()->filter ? (request()->has('filter.content_ids') ? array_merge(request()->filter, $searchable_models) : request()->filter) : $searchable_models;
        $results = [];

        foreach ($filter as $key => $value) {
            $model_name = $value;
            $column = null;
            $keywords = request()->has('keywords') ? request()->keywords : null;

            if ( ! is_numeric($key) && in_array($key, $searchable_models)) {
                $model_name = $key;
                $column = array_keys($value)[0];
                $keywords = $value[$column];
            }

            if ($key != 'content_ids' && ! method_exists($this, $method = 'get' . Str::of($model_name)->studly().'Results')) {
                throw new InvalidArgumentException();
            }

            if ($key == 'content_ids') {
                $results['contentEntry'] = $this->getContentEntryResults($keywords, $value);
            } elseif ($filter && in_array('content_ids', $filter) && $key == 'contentEntry') {
                continue;
            } else {
                $results[$model_name] = $this->{$method}($keywords, $column);
            }
        }

        return response()->json(['data' => $results]);
    }

    /** @return JsonApiResourceCollection <int, Page> */
    protected function getPageResults(string $keywords, string $column = null): JsonApiResourceCollection
    {
        $query = Page::query();

        if ($column == null) {
            foreach(Page::searchableColumns() as $key => $searchable) {
                if ($key == 0) {
                    $query->where($searchable, 'LIKE', '%'. $keywords.'%');
                }
                $query->orWhere($searchable, 'LIKE', '%'. $keywords.'%');
            }
        } else {
            $query->where($column, 'LIKE', '%'.$keywords.'%');
        }

        return PageResource::collection(
            $query->limit(20)
                ->get()
        );
    }

    /**
     * @param string $keywords
     * @param string|null $content_ids
     * @param string|null $column
     *
     * @return JsonApiResourceCollection
     */
    protected function getContentEntryResults(string $keywords, string $content_ids = null, string $column = null): JsonApiResourceCollection
    {
        $query = ContentEntry::query();

        if ($content_ids) {
            $query->whereIn('content_id', explode(',', $content_ids));
        }

        if ($column == null || $content_ids != null) {
            foreach(ContentEntry::searchableColumns() as $key => $searchable) {
                if ($key == 0) {
                    $query->where($searchable, 'LIKE', '%'. $keywords.'%');
                }
                $query->orWhere($searchable, 'LIKE', '%'. $keywords.'%');
            }
        } else {
            $query->where($column, 'LIKE', '%'.$keywords.'%');
        }

        return ContentEntryResource::collection(
            $query->limit(20)
                ->get()
        );
    }
}
