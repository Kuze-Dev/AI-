<?php

declare(strict_types=1);

namespace  Domain\Support\SlugHistory;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Builder;

trait HasSlugHistory
{
    public static function boot(): void
    {
        parent::boot();

        static::created(function ($model) {
            $slug = SlugHistory::where('slug', $model->slug)
                ->where('sluggable_type', $model->getMorphClass())->first();

            if ( ! empty($slug)) {
                $slug->sluggable_id = $model->id;
                $slug->save();
            } else {
                $model->sluggable()->updateorcreate(['slug' => $model->slug]);
            }
        });

        static::updated(function ($model) {
            $slug = SlugHistory::where('slug', $model->slug)
                ->where('sluggable_type', $model->getMorphClass())->first();

            if ( ! empty($slug)) {
                $slug->sluggable_id = $model->id;
                $slug->save();
            } else {
                $model->sluggable()->updateorcreate(['slug' => $model->slug]);
            }
        });
    }

    /** @return MorphMany<SlugHistory> */
    public function sluggable(): MorphMany
    {
        return $this->morphMany(SlugHistory::class, 'sluggable');
    }

   /**
    * @param static|Builder<static>|Relation<static> $query
    *
    * @return Relation<static>|Builder<static>
    */
    public function resolveRouteBindingQuery($query, $value, $field = null): Relation|Builder
    {
        /**
         * @phpstan-ignore-next-line
         *
         * The next line is ignore due to the framework's inconsistent typings
         */
        return $query->where($field ?? $this->getRouteKeyName(), $value)
            ->orWhereRelation('sluggable', 'slug', $value);
    }
}
