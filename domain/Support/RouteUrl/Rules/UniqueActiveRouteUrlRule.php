<?php

declare(strict_types=1);

namespace Domain\Support\RouteUrl\Rules;

use Domain\Support\RouteUrl\Contracts\HasRouteUrl;
use Illuminate\Contracts\Validation\ValidationRule;
use Closure;
use Domain\Support\RouteUrl\Models\RouteUrl;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

class UniqueActiveRouteUrlRule implements ValidationRule
{
    public function __construct(
        protected readonly ?HasRouteUrl $model
    ) {
    }

    /** @param  Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = RouteUrl::whereUrl($value)
            ->whereIn(
                'id',
                function (QueryBuilder $query) {
                    $query->select(DB::raw('MAX(updated_at)'))
                        ->from((new RouteUrl())->getTable())
                        ->groupBy('model_type', 'model_id');
                }
            );

        if ($this->model) {
//            $query->whereNotMorphedTo('model', $this->model);
            $query->where(fn (EloquentBuilder $query) => $query
                ->whereNot('model_type',  $this->model->getMorphClass())
                ->whereNot('model_id',  $this->model->getKey()));
        }

        if ($query->exists()) {
            $fail(trans('The :value is already been used.', ['value' => $value]));
        }
    }
}
