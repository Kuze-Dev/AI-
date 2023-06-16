<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Search;

use Domain\Content\Models\Content;
use Domain\Content\Models\ContentEntry;
use Domain\Page\Models\Page;
use Illuminate\Database\Eloquent\Collection as DatabaseCollection;
use Illuminate\Http\JsonResponse;
use Spatie\RouteAttributes\Attributes\Get;
use InvalidArgumentException;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class SearchController
{
    #[
        Get('/search')
    ]
    public function index(Request $request): JsonResponse
    {
        $filter = request()->filter ?? ['page', 'content', 'contentEntry'];

        $results = [];

        foreach ($filter as $key => $value) {
            if ( ! method_exists($this, $method = 'get' . Str::of($key)->studly().'Results')) {
                throw new InvalidArgumentException();
            }

            $column = array_keys($value)[0];
            $results[$key] = $this->{$method}($column, $value[$column]);
        }

        return response()->json([$results]);
    }
    
    /** @return DatabaseCollection <int, Page> */ 
    protected function getPageResults(string $column, string $keywords): DatabaseCollection
    {
        return Page::query()
            ->where($column, 'LIKE', '%'.$keywords.'%')
            ->get();
    }

    /** @return DatabaseCollection <int, Content> */ 
    protected function getContentResults(string $column, string $keywords): DatabaseCollection
    {
        return Content::query()
            ->where($column, 'LIKE', '%'.$keywords.'%')
            ->get();
    }

        /** @return DatabaseCollection <int, ContentEntry> */ 
    protected function getContentEntryResults(string $column, string $keywords): DatabaseCollection
    {
        return ContentEntry::query()
            ->where($column, 'LIKE', '%'.$keywords.'%')
            ->get();
    }
}
