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
        $model->clearMediaCollection($collection);
        foreach ($medias as $imageUrl) {
            try {
                /** @phpstan-ignore-next-line */
                $model->addMediaFromUrl($imageUrl)
                    ->toMediaCollection($collection);
            } catch (Exception $e) {
                // Log::info($e);
            }
        }
    }
}
