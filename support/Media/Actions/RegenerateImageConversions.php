<?php

declare(strict_types=1);

namespace Support\Media\Actions;

use Spatie\MediaLibrary\Conversions\FileManipulator;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class RegenerateImageConversions
{
    public function __construct(
        protected FileManipulator $fileManipulator,
    ) {

        $this->fileManipulator = $fileManipulator;
    }

    public function execute(Media $media): void
    {
        $this->fileManipulator->createDerivedFiles(
            $media,
            onlyConversionNames: [],
            onlyMissing: false,
            withResponsiveImages: false,
            queueAll: false,
        );
    }
}
