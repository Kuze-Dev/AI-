<?php

declare(strict_types=1);

namespace Domain\Media\Actions;

use Illuminate\Database\Eloquent\Model;
use Exception;
use Spatie\MediaLibrary\HasMedia;

class CreateMediaFromUrlAction
{
    public function execute(Model&HasMedia $model, array $medias, string $collection): void
    {
        $mediaExcepts = [];
        foreach ($medias as $imageUrl) {
            try {
                /** @phpstan-ignore-next-line */
                $media = $model->addMediaFromUrl($imageUrl)
                    ->toMediaCollection($collection);

                $mediaExcepts[] = $media;
            } catch (Exception $e) {
            }
        }

        $model->clearMediaCollectionExcept($collection, $mediaExcepts);
    }
}
