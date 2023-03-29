<?php

declare(strict_types=1);

namespace Domain\Support\RouteUrl\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class RouteUrl extends Model
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

    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
