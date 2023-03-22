<?php

declare(strict_types=1);

namespace Domain\Support\MetaData\Actions;

use Domain\Support\MetaData\Contracts\HasMetaData;
use Domain\Support\MetaData\DataTransferObjects\MetaDataData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;

class UpdateMetaDataAction
{
    public function execute(Model&HasMetaData $model, MetaDataData $metaDataData): Model
    {
        $defaults = $model->defaultMetaData();

        /** @var \Domain\Support\MetaData\Models\MetaData */
        $metaData = $model->metaData()->first();

        $metaData->update([
            'title' => $metaDataData->title ?? $defaults['title'] ?? null,
            'description' => $metaDataData->description ?? $defaults['description'] ?? null,
            'author' => $metaDataData->author ?? $defaults['author'] ?? null,
            'keywords' => $metaDataData->keywords ?? $defaults['keywords'] ?? null,
        ]);

        if ($metaDataData->image instanceof UploadedFile && $imageString = $metaDataData->image->get()) {
            $metaData->addMediaFromString($imageString)
                ->usingFileName($metaDataData->image->getClientOriginalName())
                ->usingName(pathinfo($metaDataData->image->getClientOriginalName(), PATHINFO_FILENAME))
                ->withCustomProperties(['alt_text' => $metaDataData->image_alt_text])
                ->toMediaCollection('image');
        } else if ($metaDataData->image !== null) {
            $metaData->getFirstMedia('image')
                ?->setCustomProperty('alt_text', $metaDataData->image_alt_text)
                ->save();
        } else {
            $metaData->clearMediaCollection('image');
        }

        return $model;
    }
}
