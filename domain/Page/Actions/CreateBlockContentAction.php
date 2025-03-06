<?php

declare(strict_types=1);

namespace Domain\Page\Actions;

use Domain\Blueprint\Actions\CreateBlueprintDataAction;
use Domain\Blueprint\Actions\UpdateBlueprintDataAction;
use Domain\Blueprint\Traits\SanitizeBlueprintDataTrait;
use Domain\Internationalization\Actions\HandleDataTranslation;
use Domain\Page\DataTransferObjects\BlockContentData;
use Domain\Page\Models\BlockContent;
use Domain\Page\Models\Page;

class CreateBlockContentAction
{
    use SanitizeBlueprintDataTrait;

    public function __construct(
        protected CreateBlueprintDataAction $createBlueprintDataAction,
        protected UpdateBlueprintDataAction $updateBlueprintDataAction,
    ) {}

    public function execute(Page $page, BlockContentData $blockContentData): BlockContent
    {
        $blockContent = $page->blockContents()->create([
            'block_id' => $blockContentData->block_id,
            'data' => $blockContentData->data,
        ]);

        $this->createBlueprintDataAction->execute($blockContent);

        if (
            \Domain\Tenant\TenantFeatureSupport::active(\App\Features\CMS\Internationalization::class) &&
            is_null($page->draftable_id)
        ) {

            app(HandleDataTranslation::class)->execute($blockContent, $blockContent);

            return $blockContent;
        }

        return $blockContent;
    }
}
