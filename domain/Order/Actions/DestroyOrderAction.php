<?php

declare(strict_types=1);

namespace Domain\Order\Actions;

use Domain\Order\Models\Order;

class DestroyOrderAction
{
    public function execute(Order $order): bool
    {
        return $order->delete();
    }
}
