<?php

declare(strict_types=1);

namespace Domain\Currency\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Domain\Currency\Models\Currency
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property float $exchange_rate
 * @property bool $enabled
 * @property bool $default
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Currency newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Currency newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Currency query()
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereExchangeRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Currency extends Model
{
    protected $fillable = [
        'code',
        'name',
        'enabled',
        'exchange_rate',
        'default',
    ];

    protected $casts = [
        'enabled' => 'bool',
        'default' => 'bool',
        'exchange_rate' => 'float',
    ];

}
