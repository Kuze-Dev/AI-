<?php

declare(strict_types=1);

namespace Domain\Page\Actions;

use Domain\Blueprint\Actions\CreateBlueprintDataAction;
use Domain\Page\DataTransferObjects\BlockContentData;
use Domain\Page\Models\Page;
use Domain\Page\Models\BlockContent;

class CreateBlockContentAction
{
    public function __construct(
        protected CreateBlueprintDataAction $createBlueprintDataAction,
    ) {
    }

    public function execute(Page $page, BlockContentData $blockContentData): BlockContent
    {
        $blockContent = $page->blockContents()->create([
            'block_id' => $blockContentData->block_id,
            'data' => $blockContentData->data,
        ]);

        $this->createBlueprintDataAction->execute($blockContent);

        return $blockContent;
    }
}
