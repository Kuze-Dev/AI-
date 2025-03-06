<?php

declare(strict_types=1);

namespace Domain\Order\Events\PublicOrder;

use Domain\Order\DataTransferObjects\GuestPlaceOrderData;
use Domain\Order\DataTransferObjects\GuestPreparedOrderData;
use Domain\Order\Models\Order;
use Illuminate\Queue\SerializesModels;

class GuestOrderPlacedEvent
{
    use SerializesModels;

    public function __construct(public Order $order, public GuestPreparedOrderData $guestPreparedOrderData, public GuestPlaceOrderData $guestPlaceOrderData) {}
}
