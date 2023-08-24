<?php

declare(strict_types=1);

namespace Domain\Media\Actions;

use Illuminate\Database\Eloquent\Model;
use Exception;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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
                        $name = $this->getMediaName($imageUrl);
                        $objectPath = $this->getBucketUrl($imageUrl);

                        if (Storage::disk('s3')->exists($objectPath)) {
                            $media = Media::where('name', $name)->first();
                            $mediaExcepts[] = $media;
                        }
                    }
                }
            } catch (Exception $e) {
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

    private function getBucketUrl(string $mediaUrl): string
    {
        /** @phpstan-ignore-next-line */
        $pathInfo = pathinfo(parse_url($mediaUrl, PHP_URL_PATH));
        $dirname = $pathInfo['dirname'] ?? '';
        $segments = explode('/', $dirname);
        array_splice($segments, 1, 1);
        $outputString = implode('/', $segments);
        $objectPath = $outputString . '/' . $pathInfo['basename'];

        return $objectPath;
    }
}
