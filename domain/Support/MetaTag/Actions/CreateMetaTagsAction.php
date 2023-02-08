<?php 

declare(strict_types=1);

namespace Domain\Support\MetaTag\Actions;

use Domain\Support\MetaTag\DataTransferObjects\MetaTagData;
use Domain\Support\MetaTag\Models\MetaTag;


class CreateMetaTagsAction
{
    public function execute(MetaTagData $metaTagData): MetaTag 
    {
        $metaTag = MetaTag::create([

        ]);

        return $metaTag;
    }
}