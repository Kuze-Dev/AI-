<?php

declare(strict_types=1);

namespace  Domain\Page\Models\Traits;

use Domain\Page\Models\RecordsSlugHistory;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

trait HasSlugHistoryTrait
{
    /** @return MorphMany<Model> */
    public function sluggable(): MorphMany
    {
        /** @var MorphMany<Model> */
        return $this->morphMany(RecordsSlugHistory::class, 'sluggable');
    }

    /**
     * Retrieve the model for a bound value.
     *
     * @param  \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\Relation  $query
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function resolveRouteBindingQuery($query, $value, $field = null): Relation
    {
        return $query->where($field ?? $this->getRouteKeyName(), $value)
            ->orWhereHas('sluggable', fn ($q) => $q->where('slug', $value));
    }
}
