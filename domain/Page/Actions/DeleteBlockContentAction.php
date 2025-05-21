<?php

declare(strict_types=1);

namespace Domain\Page\Actions;

use Domain\Blueprint\Actions\DeleteBlueprintDataAction;
use Domain\Blueprint\Models\BlueprintData;
use Domain\Page\Models\BlockContent;

class DeleteBlockContentAction
{
    public function __construct(
        protected DeleteBlueprintDataAction $deleteBlueprintDataAction,
    ) {}

    public function execute(BlockContent $blockContent): ?bool
    {
        $blueprintDataCollection = BlueprintData::where('model_id', $blockContent->id)->get();
        foreach ($blueprintDataCollection as $blueprintData) {
            $this->deleteBlueprintDataAction->execute($blueprintData);
        }

        return $blockContent->delete();
    }
}
