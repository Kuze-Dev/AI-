<?php

declare(strict_types=1);

namespace Domain\Page\Actions;

use Domain\Page\DataTransferObjects\SliceData;
use Domain\Page\Models\Slice;

class CreateSliceAction
{
    public function execute(SliceData $sliceData): Slice
    {
        return Slice::create([
            'name' => $sliceData->name,
            'component' => $sliceData->component,
            'blueprint_id' => $sliceData->blueprint_id,
            'data' => $sliceData->data,
            'is_fixed_content' => $sliceData->is_fixed_content,
        ]);
    }
}
