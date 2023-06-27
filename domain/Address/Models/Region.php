<?php

declare(strict_types=1);

namespace Domain\Address\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Region extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'country_id',
    ];

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Address\Models\Country, Region>*/
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<\Domain\Address\Models\City>*/
    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }
}
