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
        if ($media->model instanceof Customer) {
            return self::forCustomerReceipt($media);
        }

        return parent::getBasePath($media);
    }

    public static function forCustomerReceipt(Media $media): string
    {
        if ($media->collection_name !== 'receipts') {
            return parent::getBasePath($media);
        }

        $prefix = config('media-library.prefix', '');

        if ($prefix !== '') {
            return $prefix . '/' . md5($media->getKey());
        }

        return md5($media->getKey());
    }
}
