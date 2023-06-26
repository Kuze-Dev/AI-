<?php

declare(strict_types=1);

namespace Domain\Address\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class City extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'state_id',
        // Add any additional fields for the city model
    ];

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }
}
