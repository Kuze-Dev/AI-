<?php

declare(strict_types=1);

namespace Domain\Payments\Models\Traits;

use Domain\Payments\Models\Payment;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasPayments
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<\Domain\Payments\Models\Payment, $this>
     *
     * @phpstan-ignore method.childReturnType, method.childReturnType, method.childReturnType
     */
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }
}
