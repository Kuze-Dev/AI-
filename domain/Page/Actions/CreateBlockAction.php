<?php

declare(strict_types=1);

namespace Domain\Page\Actions;

use Domain\Page\DataTransferObjects\BlockData;
use Domain\Page\Models\Block;
use Illuminate\Http\UploadedFile;

class CreateBlockAction
{
    public function execute(BlockData $blockData): Block
    {
        $block = Block::create([
            'name' => $blockData->name,
            'component' => $blockData->component,
            'blueprint_id' => $blockData->blueprint_id,
            'data' => $blockData->data,
            'is_fixed_content' => $blockData->is_fixed_content,
        ]);

        if ($blockData->image instanceof UploadedFile && $imageString = $blockData->image->get()) {
            $block->addMediaFromString($imageString)
                ->usingFileName($blockData->image->getClientOriginalName())
                ->usingName(pathinfo($blockData->image->getClientOriginalName(), PATHINFO_FILENAME))
                ->toMediaCollection('image');
        }

        if ($blockData->image === null) {
            $block->clearMediaCollection('image');
        }

        $block->sites()
            ->attach($blockData->sites);

        return $block;
    }
}
