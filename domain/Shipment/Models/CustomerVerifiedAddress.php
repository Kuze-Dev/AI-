<?php

declare(strict_types=1);

namespace Domain\Shipment\Models;

use Domain\Customer\Models\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerVerifiedAddress extends Model
{
    protected $fillable = [
        'customer_id',
        'address',
        'verified_address',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
