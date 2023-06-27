<?php

declare(strict_types=1);

namespace Domain\Address\Models;

use Domain\Address\Enums\CountryStateOrRegion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Country extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'capital',
        'state_or_region',
        'timezone',
        'language',
        'active',
    ];

    protected $casts = [
        'state_or_region' => CountryStateOrRegion::class,
        'active' => 'bool',
    ];
}
