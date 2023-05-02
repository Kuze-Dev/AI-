<?php

declare(strict_types=1);

namespace Domain\Page\Actions;

use Domain\Page\DataTransferObjects\BlockContentData;
use Domain\Page\Models\BlockContent;

class UpdateBlockContentAction
{
    public function execute(BlockContent $blockContent, BlockContentData $blockContentData): BlockContent
    {
        $blockContent->update([
            'block_id' => $blockContentData->block_id,
            'data' => $blockContentData->data,
        ]);

        return $blockContent;
    }
}
