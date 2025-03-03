<?php

declare(strict_types=1);

namespace Domain\Order\Actions\PublicOrder;

use Domain\Order\DataTransferObjects\GuestPlaceOrderData;
use Domain\Order\DataTransferObjects\GuestPreparedOrderData;
use Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;

class GuestPlaceOrderAction
{
    public function __construct(
        private readonly GuestPrepareOrderAction $guestPrepareOrderAction,
        private readonly GuestSplitOrderAction $guestSplitOrderAction,
    ) {
    }

    public function execute(GuestPlaceOrderData $guestPlaceOrderData): array|Exception|HttpException
    {
        $payload = $this->guestPrepareOrderAction
            ->execute($guestPlaceOrderData);
            
        $result = $this->guestSplitOrderAction->execute($payload, $guestPlaceOrderData);

        return $result;
        
    }
}
