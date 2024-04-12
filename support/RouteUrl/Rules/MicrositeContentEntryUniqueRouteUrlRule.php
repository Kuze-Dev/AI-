<?php

declare(strict_types=1);

namespace Support\RouteUrl\Rules;

use Closure;
use Domain\Content\Models\ContentEntry;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use Support\RouteUrl\Contracts\HasRouteUrl;
use Support\RouteUrl\Models\RouteUrl;

class MicrositeContentEntryUniqueRouteUrlRule implements ValidationRule
{
    public function __construct(
        protected readonly ?HasRouteUrl $ignoreModel,
        protected readonly array $route_url,
    ) {

    }

    /** @param  Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail */
    #[\Override]
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {

        $content = ContentEntry::select('id')->wherehas('sites', fn ($q) => $q->whereIn('site_id', $value))->pluck('id')->toArray();

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

        $query->whereIN('model_id', $content)->where('url', $this->route_url['url']);

        if ($this->ignoreModel) {
            $query->whereNot(fn (EloquentBuilder $query) => $query
                ->where('model_type', $this->ignoreModel->getMorphClass())
                ->where('model_id', $this->ignoreModel->getKey()));
        }

        if ($query->exists()) {
            $fail(trans('The :value is already been used.', ['value' => $this->route_url['url']]));
        }
    }
}
