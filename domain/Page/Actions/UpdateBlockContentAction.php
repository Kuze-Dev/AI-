<?php

declare(strict_types=1);

namespace Domain\Page\Actions;

use Domain\Blueprint\Actions\UpdateBlueprintDataAction;
use Domain\Page\DataTransferObjects\BlockContentData;
use Domain\Page\Models\BlockContent;

class UpdateBlockContentAction
{
    public function __construct(
        protected UpdateBlueprintDataAction $updateBlueprintDataAction,
    ) {
    }

    public function execute(BlockContent $blockContent, BlockContentData $blockContentData): BlockContent
    {
        $blockContent->update([
            'block_id' => $blockContentData->block_id,
            'data' => $blockContentData->data,
        ]);
        $this->updateBlueprintDataAction->execute($blockContent);

        return $blockContent;
    }
}
