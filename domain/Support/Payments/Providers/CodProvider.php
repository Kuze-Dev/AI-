<?php

namespace Domain\Support\Payments\Providers;

use Domain\Support\Payments\DataTransferObjects\PayPalProviderData;
use Domain\Support\Payments\Interfaces\HandlesManual;
use Domain\Support\Payments\Interfaces\PayableInterface;

class CodProvider extends Provider implements HandlesManual
{
    protected $name = 'cod';

    public function handleManually(PayPalProviderData $providerData)
    {   
        $payable = $providerData->model;

        $payable->payment()->create([
            'gateway' => $this->getName(),
            'transaction_id' => '',
            'amount' => $providerData->transactionData->amount->total
        ]);
    }
}
