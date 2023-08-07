<?php

declare(strict_types=1);

namespace Domain\Blueprint\Models;

use Domain\Blueprint\Enums\BlueprintDataType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BlueprintData extends Model
{
    protected $casts = [
        'type' => BlueprintDataType::class,
        'value' => 'array', // TODO: DTO
    ];

    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(Blueprint::class);
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
