<?php

declare(strict_types=1);

namespace Domain\Support\SlugHistory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Eloquent;

/**
 * Domain\Support\SlugHistory\SlugHistory
 *
 * @property int $id
 * @property string $slug
 * @property string $model_type
 * @property int $model_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Model|Eloquent $model
 * @method static \Illuminate\Database\Eloquent\Builder|SlugHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SlugHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SlugHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|SlugHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SlugHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SlugHistory whereModelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SlugHistory whereModelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SlugHistory whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SlugHistory whereUpdatedAt($value)
 * @mixin Eloquent
 */
class SlugHistory extends Model
{
    protected $fillable = [
        'slug',
        'model_id',
        'model_type',
    ];

    /** @return MorphTo<Model, self> */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
