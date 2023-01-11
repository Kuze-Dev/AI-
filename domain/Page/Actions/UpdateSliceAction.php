<?php

declare(strict_types=1);

namespace Domain\Page\Actions;

use Domain\Page\DataTransferObjects\SliceData;
use Domain\Page\Models\Slice;

class UpdateSliceAction
{
    public function execute(Slice $slice, SliceData $sliceData): Slice
    {
        $slice->update([
            'name' => $sliceData->name,
            'component' => $sliceData->component,
        ]);

        return $slice;
    }
}
