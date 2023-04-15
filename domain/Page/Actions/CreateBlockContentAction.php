<?php

declare(strict_types=1);

namespace Domain\Page\Actions;

use Domain\Page\DataTransferObjects\BlockContentData;
use Domain\Page\Models\Page;
use Domain\Page\Models\BlockContent;

class CreateBlockContentAction
{
    public function execute(Page $page, BlockContentData $blockContentData): BlockContent
    {
        return $page->blockContents()->create([
            'block_id' => $blockContentData->block_id,
            'data' => $blockContentData->data,
        ]);
    }
}
