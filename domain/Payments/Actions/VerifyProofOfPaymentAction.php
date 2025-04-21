<?php

declare(strict_types=1);

namespace Domain\Payments\Actions;

use Domain\Payments\DataTransferObjects\VerifyProofOfPaymentData;
use Domain\Payments\Models\Payment;
use Support\Common\Actions\SyncMediaCollectionAction;
use Support\Common\DataTransferObjects\MediaCollectionData;

class VerifyProofOfPaymentAction
{
    public function __construct(
        protected SyncMediaCollectionAction $syncMediaCollectionAction
    ) {}

    /** Execute create collection query. */
    public function execute(Payment $model, VerifyProofOfPaymentData $verifyProofOfPaymentData): Payment
    {

        $model->update([
            'remarks' => $verifyProofOfPaymentData->remarks,
            'admin_message' => $verifyProofOfPaymentData->message,
        ]);

        $this->syncMediaCollectionAction->execute(
            model: $model,
            mediaCollectionData: MediaCollectionData::fromArray([
                'collection' => 'image',
                'media' => $verifyProofOfPaymentData->proof_of_payment ? [
                    'media' => $verifyProofOfPaymentData->proof_of_payment,
                    'custom_properties' => ['alt_text' => 'proof of payment'],
                ]
                    : [],
            ])
        );

        return $model;
    }
}
