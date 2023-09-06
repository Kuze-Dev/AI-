<?php

declare(strict_types=1);

namespace Support\RouteUrl\Rules;

use Support\RouteUrl\Contracts\HasRouteUrl;
use Illuminate\Contracts\Validation\ValidationRule;
use Closure;
use Domain\Content\Models\ContentEntry;
use Domain\Page\Models\Page;
use Support\RouteUrl\Models\RouteUrl;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

class MicroSiteUniqueRouteUrlRule implements ValidationRule
{
    public function __construct(
        protected readonly ?HasRouteUrl $ignoreModel = null,
        protected readonly array $route_url,
    ) {

    }

    /** @param  Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {

        $pages = Page::select('id')->wherehas('sites', function ($q) use ($value) {
            return $q->whereIn('site_id', $value);
        })->pluck('id')->toArray();

        $contentEntriesIds = ContentEntry::select('id')->wherehas('sites', function ($q) use ($value) {
            return $q->whereIn('site_id', $value);
        })->pluck('id')->toArray();


        $pagesIds = array_merge($pages, $contentEntriesIds);


        $query = RouteUrl::whereUrl($this->route_url['url'])
            ->whereIn(
                'id',
                RouteUrl::select('id')
                    ->where(
                        'updated_at',
                        fn (QueryBuilder $query) => $query->select(DB::raw('MAX(`updated_at`)'))
                            ->from((new RouteUrl())->getTable(), 'sub_query_table')
                            ->whereColumn('sub_query_table.model_type', 'route_urls.model_type')
                            ->whereColumn('sub_query_table.model_id', 'route_urls.model_id')
                    )
            );

        $query->whereIN('model_id', $pagesIds)
                ->where('url', $this->route_url['url']);
            
                
        if ($this->ignoreModel) {
          
            if ($this->ignoreModel instanceof Page) {
                
                if ($this->ignoreModel->parentPage) {
                    
                    $ignoreModelIds = [
                        $this->ignoreModel->getKey(),
                        $this->ignoreModel->parentPage->getKey(),
                    ];

                    $query->whereNot(fn (EloquentBuilder $query) => $query
                        ->where('model_type',  $this->ignoreModel->getMorphClass())
                        ->whereIn('model_id', $ignoreModelIds));
                }else{

                    $query->whereNot(fn (EloquentBuilder $query) => $query
                    ->where('model_type',  $this->ignoreModel->getMorphClass())
                    ->where('model_id',  $this->ignoreModel->getKey()));
                }
    
            }else{
                $query->whereNot(fn (EloquentBuilder $query) => $query
                    ->where('model_type',  $this->ignoreModel->getMorphClass())
                    ->where('model_id',  $this->ignoreModel->getKey()));
            }
          
        }

         
        if ($query->exists()) {
            $fail(trans('The :value is already been used.', ['value' => $this->route_url['url']]));
        }
    }
}
