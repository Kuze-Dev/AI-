<?php

declare(strict_types=1);

namespace Domain\Page\Actions;

use Domain\Blueprint\Actions\UpdateBlueprintDataAction;
use Domain\Page\DataTransferObjects\BlockContentData;
use Domain\Page\Models\BlockContent;
use Domain\Page\Models\Page;

class TransferBlockContentToPageAction
{
    public function __construct(
        protected UpdateBlueprintDataAction $updateBlueprintDataAction,
    ) {}

    public function execute(Page $page, BlockContentData $blockContentData): BlockContent
    {
        $blockContent = BlockContent::findorfail($blockContentData->id);

        $blockContent->update([
            'page_id' => $page->id,
            'block_id' => $blockContentData->block_id,
            'data' => $blockContentData->data,
        ]);

        $this->updateBlueprintDataAction->execute($blockContent);

        return $blockContent;
    }
}
