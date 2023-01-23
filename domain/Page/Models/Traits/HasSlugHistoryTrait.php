<?php

declare(strict_types=1);

namespace  Domain\Page\Models\Traits;

use Domain\Page\Models\RecordsSlugHistory;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasSlugHistoryTrait
{
    public function sluggable(): MorphMany
    {
        return $this->morphMany(RecordsSlugHistory::class, 'sluggable');
    }

    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        return $query->where($field ?? $this->getRouteKeyName(), $value)
            ->orWhereHas('sluggable', fn ($q) => $q->where('slug',$value));
    }
}
