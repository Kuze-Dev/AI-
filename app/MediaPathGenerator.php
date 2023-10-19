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
            /** @phpstan-ignore-next-line */
            return parent::getBasePath($media);
        }

        $prefix = config('media-library.prefix', '');

        $md5 = md5(
            $media->getKey().
            $media->model->getKey().
            $media->created_at
        );

        if ($prefix !== '') {
            return $prefix . '/' . $md5;
        }

        return $md5;
    }
}
