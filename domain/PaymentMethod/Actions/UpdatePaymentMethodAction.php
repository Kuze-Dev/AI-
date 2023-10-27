<?php

declare(strict_types=1);

namespace Domain\PaymentMethod\Actions;

use Domain\PaymentMethod\DataTransferObjects\PaymentMethodData;
use Domain\PaymentMethod\Models\PaymentMethod;
use Support\Common\Actions\SyncMediaCollectionAction;
use Support\Common\DataTransferObjects\MediaCollectionData;

class UpdatePaymentMethodAction
{
    public function __construct(
        protected SyncMediaCollectionAction $syncMediaCollectionAction
    ) {
    }

    public function execute(PaymentMethod $paymentMethod, PaymentMethodData $paymentMethodData): PaymentMethod
    {
        $paymentMethod->update([
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
