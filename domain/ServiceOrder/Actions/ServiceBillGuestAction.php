<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\ServiceOrder\Models\ServiceBill;

class ServiceBillGuestAction
{
    public function execute(ServiceBill $serviceBill): ServiceBill
    {

        $serviceBill->reference = '********'.substr($serviceBill->reference, -4);

        return $serviceBill;
    }
}
