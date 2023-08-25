<?php

declare(strict_types=1);

namespace Domain\Media\Actions;

use Illuminate\Database\Eloquent\Model;
use Exception;
use Log;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Spatie\MediaLibrary\HasMedia;

class CreateMediaAction
{
    public function execute(Model&HasMedia $model, array $medias, string $collection, bool $isCreate = true): void
    {
        if ($isCreate) {
            /** Clears unexpected media even before upload */
            $model->clearMediaCollection($collection);
        }

        foreach ($medias as $media) {
            try {
                if (is_string($media)) {
                    $response = Http::get($media);
                    if ($response->successful()) {
                        /** @phpstan-ignore-next-line */
                        $model
                            ->addMediaFromUrl($media)
                            ->toMediaCollection($collection);
                    }
                } else {
                    if ($media instanceof UploadedFile && $mediaString = $media->get()) {
                        /** @phpstan-ignore-next-line */
                        $model
                            ->addMediaFromString($mediaString)
                            ->usingFileName($media->getClientOriginalName())
                            ->usingName(pathinfo($media->getClientOriginalName(), PATHINFO_FILENAME))
                            ->toMediaCollection($collection);
                    }
                }
            } catch (Exception $e) {
                Log::info('Error on CreateMediaAction->execute() ' . $e);
            }
        }

        if ( ! $isCreate) {
            $excludedMedia = $model->getMedia($collection)->whereIn('uuid', $medias);
            $model->clearMediaCollectionExcept($collection, $excludedMedia);
        }

        if ( ! filled($medias)) {
            $model->clearMediaCollection($collection);
        }
    }
}
