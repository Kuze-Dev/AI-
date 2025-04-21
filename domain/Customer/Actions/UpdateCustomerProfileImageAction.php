<?php

declare(strict_types=1);

namespace Domain\Customer\Actions;

use Domain\Customer\Models\Customer;
use Illuminate\Http\UploadedFile;
use Support\Common\Actions\SyncMediaCollectionAction;
use Support\Common\DataTransferObjects\MediaCollectionData;
use Support\Common\DataTransferObjects\MediaData;

class UpdateCustomerProfileImageAction
{
    public function __construct(
        private readonly SyncMediaCollectionAction $syncMediaCollection,
    ) {}

    public function execute(Customer $customer, UploadedFile $uploadedFile): bool
    {
        $media = $this->syncMediaCollection->execute($customer, new MediaCollectionData(
            collection: 'image',
            media: [
                new MediaData(media: $uploadedFile),
            ],
        ));

        return filled($media);
    }
}
