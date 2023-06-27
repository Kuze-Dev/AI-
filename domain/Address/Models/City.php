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
    ];

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Address\Models\State, City> */
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Address\Models\Region, City> */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }
}
