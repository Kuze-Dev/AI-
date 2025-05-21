<?php

declare(strict_types=1);

namespace Domain\Discount\Actions;

use Domain\Customer\Models\Customer;
use Domain\Discount\Models\Discount;
use Domain\Discount\Models\DiscountLimit;
use Domain\Order\Models\Order;

final class CreateDiscountLimitAction
{
    /** Execute create content query. */
    public function execute(Discount $discount, Order $order, ?Customer $customer): void
    {

        if (! is_null($discount->max_uses)) {
            $discount->update([
                'max_uses' => $discount->max_uses - 1,
            ]);
        }

        $discountLimit = new DiscountLimit;

        $discountLimit->create([
            'discount_id' => $discount->getKey(),
            'customer_id' => $customer ? $customer->getKey() : null,
            'customer_type' => $customer ? $customer->getMorphClass() : null,
            'order_id' => $order->getKey(),
            'order_type' => $order->getMorphClass(),
            'code' => $discount->code,
        ]);
    }
}
