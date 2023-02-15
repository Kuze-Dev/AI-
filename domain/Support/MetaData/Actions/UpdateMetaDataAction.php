<?php

declare(strict_types=1);

namespace Domain\Support\MetaData\Actions;

use Domain\Support\MetaData\Contracts\HasMetaData;
use Domain\Support\MetaData\DataTransferObjects\MetaDataData;
use Illuminate\Database\Eloquent\Model;

class UpdateMetaDataAction
{
    public function execute(Model&HasMetaData $model, MetaDataData $metaDataData): Model
    {
        $model->metaData
            ->update([
                'title' => $metaDataData->title ?? $model->defaultMetaData()['title'] ?? null,
                'description' => $metaDataData->description,
                'author' => $metaDataData->author,
                'keywords' => $metaDataData->keywords,
            ]);

        return $model;
    }
}
