<?php

declare(strict_types=1);

namespace Domain\Shipment\Models;

use Domain\Customer\Models\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Domain\Shipment\Models\CustomerVerifiedAddress
 *
 * @property int $id
 * @property int $customer_id
 * @property array $address
 * @property array|null $verified_address
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Customer $customer
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerVerifiedAddress newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerVerifiedAddress newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerVerifiedAddress query()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerVerifiedAddress whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerVerifiedAddress whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerVerifiedAddress whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerVerifiedAddress whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerVerifiedAddress whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerVerifiedAddress whereVerifiedAddress($value)
 * @mixin \Eloquent
 */
class CustomerVerifiedAddress extends Model
{
    protected $fillable = [
        'customer_id',
        'address',
        'verified_address',
    ];

    protected $casts = [
        'address' => 'array',
        'verified_address' => 'array',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
