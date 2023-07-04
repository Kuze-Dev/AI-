<?php

declare(strict_types=1);

namespace Domain\Discount\Models;

use Illuminate\Database\Eloquent\Model;

class DiscountLimit extends Model
{
    protected $fillable = [
        'discount_id',
        'user_type',
        'user_id',
        'order_type',
        'order_id',
        'code',
    ];
}
