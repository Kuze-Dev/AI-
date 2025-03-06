<?php

declare(strict_types=1);

namespace Domain\Order\Events;

use Domain\Order\DataTransferObjects\PlaceOrderData;
use Domain\Order\DataTransferObjects\PreparedOrderData;
use Domain\Order\Models\Order;
use Illuminate\Queue\SerializesModels;

class OrderPlacedEvent
{
    use SerializesModels;

    public function __construct(public Order $order, public PreparedOrderData $preparedOrderData, public PlaceOrderData $placeOrderData) {}
}
