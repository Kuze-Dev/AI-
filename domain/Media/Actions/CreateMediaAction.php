<?php

declare(strict_types=1);

namespace Domain\Media\Actions;

use Illuminate\Database\Eloquent\Model;
use Exception;
use Log;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

class CreateMediaAction
{
    public function execute(Model $model, array $medias, string $collection, bool $isCreate = true): void
    {
        if ($isCreate) {
            /** Clears unexpected media even before upload */
            $model->clearMediaCollection($collection);
        }

        foreach ($medias as $image) {
            try {
                if (is_string($image)) {
                    $response = Http::get($image);
                    if ($response->successful()) {
                        $model
                            ->addMediaFromUrl($image)
                            ->toMediaCollection($collection);
                    }
                } else {
                    if ($image instanceof UploadedFile && $imageString = $image->get()) {
                        $model
                            ->addMediaFromString($imageString)
                            ->usingFileName($image->getClientOriginalName())
                            ->usingName(pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME))
                            ->toMediaCollection($collection);
                    }
                }
            } catch (Exception $e) {
                Log::info($e);
            }
        }

        if (!$isCreate) {
            $excludedMedia = $model->getMedia($collection)->whereIn('uuid', $medias);
            $model->clearMediaCollectionExcept($collection, $excludedMedia);
        }

        if (!filled($medias)) {
            $model->clearMediaCollection($collection);
        }
    }
}
