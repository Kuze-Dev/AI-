<?php

declare(strict_types=1);

namespace Domain\Page\Actions;

use Domain\Blueprint\Models\Blueprint;
use Domain\Blueprint\Traits\SanitizeBlueprintDataTrait;
use Domain\Page\DataTransferObjects\BlockData;
use Domain\Page\Models\Block;
use Illuminate\Http\UploadedFile;

class UpdateBlockAction
{
    use SanitizeBlueprintDataTrait;

    public function execute(Block $block, BlockData $blockData): Block
    {
        /** @var Blueprint|null */
        $blueprint = Blueprint::whereId($block->blueprint_id)->first();

        if (! $blueprint) {
            abort(422, 'Cannot Access Blueprint '.$block->blueprint_id);
        }

        $sanitizeData = $this->sanitizeBlueprintData(
            $blockData->data,
            $blueprint->schema->getFieldStatekeys()
        );

        $block->update([
            'name' => $blockData->name,
            'component' => $blockData->component,
            'data' => $sanitizeData,
            'is_fixed_content' => $blockData->is_fixed_content,
        ]);

        if ($blockData->image instanceof UploadedFile && $imageString = $blockData->image->get()) {
            $block->addmedia($blockData->image)
                ->usingFileName($blockData->image->getClientOriginalName())
                ->usingName(pathinfo($blockData->image->getClientOriginalName(), PATHINFO_FILENAME))
                ->toMediaCollection('image');
        }

        if ($blockData->image === null) {
            $block->clearMediaCollection('image');
        }

        $block->sites()
            ->sync($blockData->sites);

        return $block;
    }
}
