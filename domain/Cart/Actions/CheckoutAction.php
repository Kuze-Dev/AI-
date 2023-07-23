<?php

declare(strict_types=1);

namespace Domain\Cart\Actions;

use Domain\Cart\DataTransferObjects\CheckoutData;
use Domain\Cart\Models\CartLine;
use Illuminate\Support\Str;

class CheckoutAction
{
    public function execute(CheckoutData $checkoutData): string
    {
        $checkoutReference = (string) Str::uuid();

        CartLine::whereIn('uuid', $checkoutData->cart_line_ids)
            ->update([
                'checkout_reference' => $checkoutReference,
                'checkout_expiration' => now()->addMinutes(20),
            ]);

        return $checkoutReference;
    }
}
