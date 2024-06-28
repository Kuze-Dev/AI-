<?php

declare(strict_types=1);

namespace Domain\Page\Actions;

use Domain\Blueprint\Actions\UpdateBlueprintDataAction;
use Domain\Blueprint\Traits\SanitizeBlueprintDataTrait;
use Domain\Page\DataTransferObjects\BlockContentData;
use Domain\Page\Models\BlockContent;

class UpdateBlockContentAction
{
    use SanitizeBlueprintDataTrait;

    public function __construct(
        protected UpdateBlueprintDataAction $updateBlueprintDataAction,
    ) {
    }

    public function execute(BlockContent $blockContent, BlockContentData $blockContentData): BlockContent
    {
        $sanitizeData = $this->sanitizeBlueprintData(
            $blockContentData->data ?? [],
            $blockContent->block->blueprint->schema->getFieldStatekeys()
        );

        $blockContent->update([
            'block_id' => $blockContentData->block_id,
            'data' => $sanitizeData ? $sanitizeData : null,
        ]);
        $this->updateBlueprintDataAction->execute($blockContent);

        return $blockContent;
    }
}
