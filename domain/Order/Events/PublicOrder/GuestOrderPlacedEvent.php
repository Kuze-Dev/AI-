<?php

declare(strict_types=1);

namespace Domain\Order\Events\PublicOrder;

use Domain\Order\DataTransferObjects\GuestPreparedOrderData;
use Domain\Order\DataTransferObjects\GuestPlaceOrderData;
use Domain\Order\Models\Order;
use Illuminate\Queue\SerializesModels;

class GuestOrderPlacedEvent
{
    use SerializesModels;

    public Order $order;
    public GuestPreparedOrderData $guestPreparedOrderData;
    public GuestPlaceOrderData $guestPlaceOrderData;

    public function __construct(
        Order $order,
        GuestPreparedOrderData $guestPreparedOrderData,
        GuestPlaceOrderData $guestPlaceOrderData
    ) {
        $this->order = $order;
        $this->guestPreparedOrderData = $guestPreparedOrderData;
        $this->guestPlaceOrderData = $guestPlaceOrderData;
    }
}
