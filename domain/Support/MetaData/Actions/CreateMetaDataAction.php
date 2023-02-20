<?php

declare(strict_types=1);

namespace Domain\Support\MetaData\Actions;

use Domain\Support\MetaData\Contracts\HasMetaData;
use Domain\Support\MetaData\DataTransferObjects\MetaDataData;
use Illuminate\Database\Eloquent\Model;

class CreateMetaDataAction
{
    public function execute(Model&HasMetaData $model, MetaDataData $metaDataData): Model
    {
        $defaults = $model->defaultMetaData();

        $model->metaData()
            ->create([
                'title' => $metaDataData->title ?? $defaults['title'] ?? null,
                'description' => $metaDataData->description ?? $defaults['description'] ?? null,
                'author' => $metaDataData->author ?? $defaults['author'] ?? null,
                'keywords' => $metaDataData->keywords ?? $defaults['keywords'] ?? null,
            ]);

        return $model;
    }
}
