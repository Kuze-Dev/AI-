<?php

declare(strict_types=1);

namespace Domain\Discount\Actions;

use Domain\Customer\Models\Customer;
use Domain\Discount\Enums\DiscountStatus;
use Domain\Discount\Models\Discount;
use Domain\Discount\Models\DiscountLimit;
use Domain\Order\Models\Order;

final class CreateDiscountLimitAction
{
    /** Execute create content query. */
    public function execute(string $discountCode, Order $order, Customer $customer): void
    {
        $discount = Discount::whereCode($discountCode)
            ->whereStatus(DiscountStatus::ACTIVE)
            ->where(function ($query) {
                $query->where('max_uses', '>', 0)
                    ->orWhereNull('max_uses');
            })
            ->firstOrFail();

        $count = DiscountLimit::whereDiscountId($discount->id)->count();

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
