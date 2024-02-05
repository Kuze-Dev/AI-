<?php

declare(strict_types=1);

namespace Domain\Page\Actions;

use Domain\Page\DataTransferObjects\BlockData;
use Domain\Page\Models\Block;
use Illuminate\Http\UploadedFile;

class UpdateBlockAction
{
    public function execute(Block $block, BlockData $blockData): Block
    {
        $block->update([
            'name' => $blockData->name,
            'component' => $blockData->component,
            'data' => $blockData->data,
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

        return $block;
    }
}
