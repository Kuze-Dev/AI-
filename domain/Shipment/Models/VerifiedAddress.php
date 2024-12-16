<?php

declare(strict_types=1);

namespace Domain\Shipment\Models;

use Domain\Customer\Models\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Domain\Shipment\Models\VerifiedAddress
 *
 * @property int $id
 * @property int $customer_id
 * @property array $address
 * @property array|null $verified_address
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Customer|null $customer
 *
 * @method static \Illuminate\Database\Eloquent\Builder|VerifiedAddress newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VerifiedAddress newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VerifiedAddress query()
 * @method static \Illuminate\Database\Eloquent\Builder|VerifiedAddress whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VerifiedAddress whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VerifiedAddress whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VerifiedAddress whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VerifiedAddress whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VerifiedAddress whereVerifiedAddress($value)
 *
 * @mixin \Eloquent
 */
class VerifiedAddress extends Model
{
    protected $fillable = [
        'customer_id',
        'address',
        'verified_address',
    ];

    protected function casts(): array
    {
        return [
            'address' => 'array',
            'verified_address' => 'array',
        ];
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Customer\Models\Customer, $this>*/
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
