<?php

declare(strict_types=1);

namespace Domain\Media\Actions;

use Illuminate\Database\Eloquent\Model;
use Exception;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Support\Str;

class CreateMediaFromS3UrlAction
{
    public function execute(Model&HasMedia $model, array $medias, string $collection): void
    {
        $mediaExcepts = [];
        foreach ($medias as $imageUrl) {
            try {
                if (Str::contains($imageUrl, 'tmp/')) {
                    if (Storage::disk('s3')->exists($imageUrl)) {
                        /** @phpstan-ignore-next-line */
                        $media = $model->addMediaFromDisk($imageUrl, 's3')
                            ->toMediaCollection($collection);

                        $mediaExcepts[] = $media;
                    }
                } else {
                    if (Str::contains($imageUrl, env('AWS_ENDPOINT'))) {
                        /** @phpstan-ignore-next-line */
                        $media = $model->addMediaFromUrl($imageUrl)
                            ->toMediaCollection($collection);

                        $mediaExcepts[] = $media;
                    }
                }
            } catch (Exception $e) {
            }
        }

        $model->clearMediaCollectionExcept($collection, $mediaExcepts);
    }
}
