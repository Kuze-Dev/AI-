<?php

declare(strict_types=1);

namespace Domain\Media\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CreateMediaFromS3UrlAction
{
    public function execute(Model&HasMedia $model, array $medias, string $collection): void
    {
        $mediaExcepts = [];
        foreach ($medias as $imageUrl) {
            if (Str::contains($imageUrl, 'tmp/')) {
                if (Storage::disk(config('filament.default_filesystem_disk'))->exists($imageUrl)) {
                    /** @phpstan-ignore-next-line */
                    $media = $model->addMediaFromDisk($imageUrl, config('filament.default_filesystem_disk'))
                        ->toMediaCollection($collection);

                    $mediaExcepts[] = $media;
                }
            } else {
                $name = $this->getMediaName($imageUrl);

                $media = Media::where('name', $name)->first();
                $mediaExcepts[] = $media;
            }
        }

        $model->clearMediaCollectionExcept($collection, $mediaExcepts);
    }

    private function getMediaName(string $mediaUrl): string
    {
        /** @phpstan-ignore-next-line */
        $pathInfo = pathinfo(parse_url($mediaUrl, PHP_URL_PATH));

        if (Str::contains($pathInfo['filename'], '-preview')) {
            $extractedString = explode('-', $pathInfo['filename'])[0];

            return $extractedString;
        }

        return $pathInfo['filename'];
    }
}
