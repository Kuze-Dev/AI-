<?php

declare(strict_types=1);

namespace Domain\Country\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;

class Country extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'code',
        'name',
        'capital',
        'timezone',
        'language',
        'active',
    ];

    protected static function boot()
    {
        parent::boot();

        Relation::morphMap([
            'currency' => 'Domain\Country\Models\Country',
        ]);

    }
}
