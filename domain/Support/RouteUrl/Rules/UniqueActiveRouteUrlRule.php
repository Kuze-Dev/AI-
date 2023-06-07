<?php

declare(strict_types=1);

namespace Domain\Support\RouteUrl\Rules;

use Domain\Support\RouteUrl\Contracts\HasRouteUrl;
use Illuminate\Contracts\Validation\ValidationRule;
use Closure;
use Domain\Support\RouteUrl\Models\RouteUrl;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

class UniqueActiveRouteUrlRule implements ValidationRule
{
    public function __construct(
        protected readonly ?HasRouteUrl $ignoreModel = null
    ) {
    }

    /** @param  Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = RouteUrl::whereUrl($value)
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

        if ($this->ignoreModel) {
            $query->whereNot(fn (EloquentBuilder $query) => $query
                ->where('model_type',  $this->ignoreModel->getMorphClass())
                ->where('model_id',  $this->ignoreModel->getKey()));
        }

        if ($query->exists()) {
            $fail(trans('The :value is already been used.', ['value' => $value]));
        }
    }
}
