<?php

declare(strict_types=1);

namespace Domain\Order\Actions;

use Domain\Order\DataTransferObjects\PlaceOrderData;
use Domain\Order\DataTransferObjects\PreparedOrderData;
use Domain\Order\Enums\PlaceOrderResult;

class PlaceOrderAction
{
    public function execute(PlaceOrderData $placeOrderData)
    {
        $payload = app(PrepareOrderAction::class)
            ->execute($placeOrderData);

        return $payload;
        if ($payload instanceof PreparedOrderData) {
            $result = app(SplitOrderAction::class)->execute($payload, $placeOrderData);
            return $result;
        }

        return PlaceOrderResult::FAILED;
    }
}
