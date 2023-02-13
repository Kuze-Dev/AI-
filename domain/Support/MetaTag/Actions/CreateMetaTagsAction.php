<?php

declare(strict_types=1);

namespace Domain\Support\MetaTag\Actions;

use Domain\Support\MetaTag\DataTransferObjects\MetaTagData;
use Illuminate\Database\Eloquent\Model;

class CreateMetaTagsAction
{
    public function execute(Model $model, MetaTagData $metaTagData): Model
    {
        $model->metaTags()
            ->create([
                'title' => $metaTagData->title == null ? $model->slug : $metaTagData->title,
                'description' => $metaTagData->description,
                'author' => $metaTagData->author,
                'keywords' => $metaTagData->keywords,
            ]);

        return $model;
    }
}
