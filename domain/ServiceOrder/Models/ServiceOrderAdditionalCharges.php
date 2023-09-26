<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Models;

use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Illuminate\Database\Eloquent\Model;


/**
 * @property mixed|null $blueprint
 */
class ServiceOrderAdditionalCharges extends Model 
{
    protected $fillable = [
        'name',
        'quantity',
        'price',
    ];

    protected $casts = [
        'price' => 'float',
    ];
}
