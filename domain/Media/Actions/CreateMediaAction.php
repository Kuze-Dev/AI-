<?php

declare(strict_types=1);

namespace Domain\Media\Actions;

use Illuminate\Database\Eloquent\Model;
use Exception;

class CreateMediaAction
{
    public function execute(Model $model, array $medias, string $collection)
    {
        $model->clearMediaCollection($collection);
        foreach ($medias as $imageUrl) {
            try {
                $model->addMediaFromUrl($imageUrl)
                    ->toMediaCollection($collection);
            } catch (Exception $e) {
                \Log::info($e);
            }
        }
    }
}
