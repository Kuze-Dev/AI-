<?php

declare(strict_types=1);

namespace Domain\Support\MetaData\Actions;

use Domain\Support\MetaData\DataTransferObjects\MetaDataData;
use Illuminate\Database\Eloquent\Model;

class UpdateMetaDataAction
{
    public function execute(Model $model, MetaDataData $metaDataData): Model
    {
        $model->metaTags
            ->update([
                'title' => $metaDataData->title == null ? $model->slug : $metaDataData->title,
                'description' => $metaDataData->description,
                'author' => $metaDataData->author,
                'keywords' => $metaDataData->keywords,
            ]);

        return $model;
    }
}
