<?php

declare(strict_types=1);

namespace Support\RouteUrl\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use Support\RouteUrl\Contracts\HasRouteUrl;
use Support\RouteUrl\EloquentBuilder\RouteUrlEloquentBuilder;
use Support\RouteUrl\Models\RouteUrl;

class UniqueActiveRouteUrlRule implements ValidationRule
{
    public function __construct(
        protected readonly ?HasRouteUrl $ignoreModel = null
    ) {
    }

    /** @param  Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail */
    #[\Override]
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = RouteUrl::whereUrl($value)
            ->whereIn(
                'id',
                RouteUrl::query()
                    ->select('id')
                    ->where(
                        'updated_at',
                        fn (RouteUrlEloquentBuilder $query) => $query->select(DB::raw('MAX(`updated_at`)'))
                            ->from((new RouteUrl())->getTable(), 'sub_query_table')
                            ->whereColumn('sub_query_table.model_type', 'route_urls.model_type')
                            ->whereColumn('sub_query_table.model_id', 'route_urls.model_id')
                    )
            );

        if ($this->ignoreModel !== null) {

            if ($this->ignoreModel->parentPage ?? false) {

                $ignoreModelIds = [
                    $this->ignoreModel->getKey(),
                    $this->ignoreModel->parentPage->getKey(),
                ];

            } elseif ($this->ignoreModel->pageDraft ?? false) {

                $ignoreModelIds = [
                    $this->ignoreModel->getKey(),
                    $this->ignoreModel->pageDraft->getKey(),
                ];

            } else {

                $ignoreModelIds = [
                    $this->ignoreModel->getKey(),
                ];

            }

            $query->whereNot(
                fn (RouteUrlEloquentBuilder $query): EloquentBuilder|RouteUrlEloquentBuilder => $query
                    ->where('model_type', $this->ignoreModel->getMorphClass())
                    ->whereIn('model_id', $ignoreModelIds)
            );

        }

        if ($query->exists()) {
            $fail(trans('The :value is already been used.', ['value' => $value]));
        }
    }
}
