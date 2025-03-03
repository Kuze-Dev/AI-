<?php

declare(strict_types=1);

namespace Domain\Order\Actions;

use Domain\Order\DataTransferObjects\PlaceOrderData;
use Domain\Order\DataTransferObjects\PreparedOrderData;

class PlaceOrderAction
{
    public function __construct(
        private readonly PrepareOrderAction $prepareOrderAction,
        private readonly SplitOrderAction $splitOrderAction,
    ) {
    }

    public function execute(PlaceOrderData $placeOrderData): array
    {
        $payload = $this->prepareOrderAction
            ->execute($placeOrderData);

        $result = $this->splitOrderAction->execute($payload, $placeOrderData);

        return $result;
    }
}
