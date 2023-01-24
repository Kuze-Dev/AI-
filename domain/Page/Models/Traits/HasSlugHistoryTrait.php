<?php

declare(strict_types=1);

namespace  Domain\Page\Models\Traits;

use Domain\Page\Models\RecordsSlugHistory;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Builder;

/**  
 * @template TRelatedModel of RecordsSlugHistory
 * 
 */
trait HasSlugHistoryTrait
{
    public static function boot(): void
    {
        parent::boot();

        static::created(function ($model) {
            $slug = RecordsSlugHistory::where('slug', $model->slug)
                ->where('sluggable_type', $model->getMorphClass())->first();

            if ( ! empty($slug)) {
                $slug->sluggable_id = $model->id;
                $slug->save();
            } else {
                $model->sluggable()->updateorcreate(['slug' => $model->slug]);
            }
        });

        static::updated(function ($model) {
            $slug = RecordsSlugHistory::where('slug', $model->slug)
                ->where('sluggable_type', $model->getMorphClass())->first();

            if ( ! empty($slug)) {
                $slug->sluggable_id = $model->id;
                $slug->save();
            } else {
                $model->sluggable()->updateorcreate(['slug' => $model->slug]);
            }
        });
    }

    /** @return MorphMany<Model> */
    public function sluggable(): MorphMany
    {
        /** @var MorphMany<Model> */
        return $this->morphMany(RecordsSlugHistory::class, 'sluggable');
    }

   /**
    * @param static|Relation<static> $query
    *
    * @return Relation|Builder<static> 
    */
    public function resolveRouteBindingQuery( $query, $value, $field = null) : Builder
    {
        return $query->where($field ?? $this->getRouteKeyName(), $value)
            ->orWhereHas('sluggable', fn ($q) => $q->where('slug', $value));
    }
}
