<?php

declare(strict_types=1);

namespace Domain\Order\Models;

use Domain\Address\Enums\AddressLabelAs;
use Domain\Order\Enums\OrderAddressTypes;
use Illuminate\Database\Eloquent\Model;

class OrderAddress extends Model
{
    protected $fillable = [
        'order_id',
        'type',
        'state',
        'label_as',
        'address_line_1',
        'zip_code',
        'city',
    ];

    protected $casts = [
        'label_as' => AddressLabelAs::class,
        'type' => OrderAddressTypes::class,
    ];

    // Define the relationship with the Order model
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
