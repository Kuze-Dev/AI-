<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ServiceOrderResource\Rules;

use Closure;
use Domain\ServiceOrder\Enums\PaymentPlanValue;
use Illuminate\Contracts\Validation\ValidationRule;

readonly class PaymentPlanAmountRule implements ValidationRule
{
    public function __construct(
        protected float $total_price,
        protected PaymentPlanValue $payment_value,
    ) {
    }

    #[\Override]
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $amounts = array_column($value, 'amount');
        $sum = array_sum(array_map('floatval', $amounts));

        if ($this->payment_value === PaymentPlanValue::FIXED) {
            if ($this->total_price !== $sum) {
                $fail('The payment_plan amount must be equal to total price.');

            }
        } elseif ($this->payment_value === PaymentPlanValue::PERCENT) {
            if ($sum !== floatval(100)) {
                $fail('The payment_plan amount must be equal to 100.');
            }
        }

    }
}
