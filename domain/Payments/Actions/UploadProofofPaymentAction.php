<?php

declare(strict_types=1);

namespace Domain\Payments\Actions;

use Domain\Payments\DataTransferObjects\ProofOfPaymentData;
use Domain\Payments\Models\Payment;
use Support\Common\Actions\SyncMediaCollectionAction;
use Support\Common\DataTransferObjects\MediaCollectionData;

class UploadProofofPaymentAction
{
    public function __construct(
        protected SyncMediaCollectionAction $syncMediaCollectionAction
    ) {}

    /** Execute create collection query. */
    public function execute(Payment $model, ProofOfPaymentData $MarkAsPaidData): Payment
    {
        $this->syncMediaCollectionAction->execute(
            model: $model,
            mediaCollectionData: MediaCollectionData::fromArray([
                'collection' => 'image',
                'media' => $MarkAsPaidData->proof_of_payment ? [
                    'media' => $MarkAsPaidData->proof_of_payment,
                    'custom_properties' => ['alt_text' => 'proof of payment'],
                ]
                    : [],
            ])
        );

        return $model;
    }
}
