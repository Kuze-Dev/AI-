<?php

declare(strict_types=1);

namespace Domain\Blueprint\Models;

use Domain\Blueprint\Models\Casts\SchemaDataCast;
use Illuminate\Database\Eloquent\Model;

/**
 * Domain\Blueprint\Models\Blueprint
 *
 * @property \Domain\Blueprint\DataTransferObjects\SchemaData $schema
 * @method static \Illuminate\Database\Eloquent\Builder|Blueprint newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Blueprint newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Blueprint query()
 * @mixin \Eloquent
 */
class Blueprint extends Model
{
    protected $fillable = [
        'name',
        'schema',
    ];

    protected $casts = [
        'schema' => SchemaDataCast::class,
    ];
}
