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
 * @property-read Model|Eloquent $model
 * @method static \Illuminate\Database\Eloquent\Builder|RouteUrl newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RouteUrl newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RouteUrl query()
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
