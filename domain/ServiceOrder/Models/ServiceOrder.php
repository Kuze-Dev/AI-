<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Models;

use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Illuminate\Database\Eloquent\Model;


/**
 * @property mixed|null $blueprint
 */
class ServiceOrder extends Model 
{
    protected $fillable = [
        'schedule',
        'status',
    ];

    protected $casts = [
        'schedule' => 'dateTime',
        'status' => ServiceOrderStatus::class
    ];
}
