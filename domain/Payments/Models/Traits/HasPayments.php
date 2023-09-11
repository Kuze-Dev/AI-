<?php

declare(strict_types=1);

namespace Domain\Payments\Models\Traits;

use Domain\Payments\Models\Payment;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasPayments
{
    /** @return MorphMany<Payment> */
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }
}
