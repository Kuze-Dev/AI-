<?php

declare(strict_types=1);

namespace App;

use Domain\Customer\Models\Customer;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator;

class MediaPathGenerator extends DefaultPathGenerator
{
    protected function getBasePath(Media $media): string
    {
        if ($media->model_type === Customer::class) {
            return $this->forCustomerReceipt($media);
        }

        return $this->getBasePath($media);
    }

    private function forCustomerReceipt(Media $media): string
    {
        if ($media->collection_name !== 'receipts') {

            return $this->getBasePath($media);
        }

        $prefix = config('media-library.prefix', '');

        $md5 = md5(
            $media->getKey().
            $media->model_id.
            $media->created_at
        );

        if ($prefix !== '') {
            return $prefix.'/'.$md5;
        }

        return $md5;
    }
}
