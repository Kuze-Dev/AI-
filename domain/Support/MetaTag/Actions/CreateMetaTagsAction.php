<?php 

declare(strict_types=1);

namespace Domain\Support\MetaTag\Actions;

use Domain\Support\MetaTag\DataTransferObjects\MetaTagData;
use Domain\Support\MetaTag\Models\MetaTag;
use Illuminate\Database\Eloquent\Model;

class CreateMetaTagsAction
{
    public function execute(MetaTagData $metaTagData): Model 
    {
        $metaTags = $metaTagData->model->metaTags()
            ->create([
                'title' => $metaTagData->meta_title == null ? $metaTagData->model->slug : $metaTagData->meta_title,
                'description' => $metaTagData->meta_description,
                'author' => $metaTagData->meta_author,
                'keywords' => $metaTagData->meta_keywords
            ]);
        return $metaTags;
    }
}