<?php

declare(strict_types=1);

namespace Domain\Order\Actions\PublicOrder;

use Domain\Order\DataTransferObjects\GuestPlaceOrderData;
use Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;

class GuestPlaceOrderAction
{
    public function execute(GuestPlaceOrderData $guestPlaceOrderData): array|Exception|HttpException
    {
        $payload = app(GuestPrepareOrderAction::class)
            ->execute($guestPlaceOrderData);

        dd($payload);
        // if ($payload instanceof PreparedOrderData) {
        //     $result = app(SplitOrderAction::class)->execute($payload, $guestPlaceOrderData);

        //     return $result;
        // }
    }
}
