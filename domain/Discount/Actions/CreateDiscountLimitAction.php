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
    public function execute(Discount $discount, Order $order, Customer $customer): void
    {

        $discount->update([
            'max_uses' => $discount->max_uses - 1,
        ]);

        $discountLimit = new DiscountLimit();

        $discountLimit->create([
            'discount_id' => $discount->id,
            'customer_id' => $customer->id,
            'customer_type' => Customer::class,
            'order_id' => $order->id,
            'order_type' => Order::class,
            'code' => $discount->code,
        ]);
    }
}
