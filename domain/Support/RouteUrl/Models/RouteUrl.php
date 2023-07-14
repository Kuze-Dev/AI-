<?php

declare(strict_types=1);

namespace Domain\Support\RouteUrl\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Eloquent;
use Stringable;

/**
 * Domain\Support\RouteUrl\Models\RouteUrl
 *
 * @property int $id
 * @property string $model_type
 * @property int $model_id
 * @property string $url
 * @property bool $is_override
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Model|Eloquent $model
 * @method static \Illuminate\Database\Eloquent\Builder|RouteUrl newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RouteUrl newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RouteUrl query()
 * @method static \Illuminate\Database\Eloquent\Builder|RouteUrl whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RouteUrl whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RouteUrl whereIsOverride($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RouteUrl whereModelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RouteUrl whereModelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RouteUrl whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RouteUrl whereUrl($value)
 * @mixin Eloquent
 */
class RouteUrl extends Model implements Stringable
{
    protected $fillable = [
        'model_type',
        'model_id',
        'url',
        'is_override',
    ];

    protected $casts = [
        'is_override' => 'bool',
    ];

    /** @return MorphTo<Model, self> */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function __toString(): string
    {
        return $this->url;
    }
}
