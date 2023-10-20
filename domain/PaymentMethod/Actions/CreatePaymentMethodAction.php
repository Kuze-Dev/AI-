<?php

declare(strict_types=1);

namespace Domain\PaymentMethod\Actions;

use Domain\PaymentMethod\DataTransferObjects\PaymentMethodData;
use Domain\PaymentMethod\Models\PaymentMethod;
use Support\Common\Actions\SyncMediaCollectionAction;
use Support\Common\DataTransferObjects\MediaCollectionData;

class CreatePaymentMethodAction
{
    public function __construct(
        protected SyncMediaCollectionAction $syncMediaCollectionAction
    ) {
    }

    public function execute(PaymentMethodData $paymentMethodData): PaymentMethod
    {
        $paymentMethod = PaymentMethod::create([
            'title' => $paymentMethodData->title,
            'subtitle' => $paymentMethodData->subtitle,
            'gateway' => $paymentMethodData->gateway,
            'description' => $paymentMethodData->description,
            'instruction' => $paymentMethodData->instruction,
            'status' => $paymentMethodData->status,
        ]);

        $this->syncMediaCollectionAction->execute(
            $paymentMethod,
            MediaCollectionData::fromArray([
                'collection' => 'logo',
                'media' => $paymentMethodData->logo
                    ? [
                        'media' => $paymentMethodData->logo,
                        'custom_properties' => ['alt_text' => $paymentMethod->title],
                    ]
                    : [],
            ])
        );

        return $paymentMethod;
    }
}
