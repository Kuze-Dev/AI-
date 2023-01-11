<?php

declare(strict_types=1);

namespace Domain\Page\Actions;

use Domain\Page\DataTransferObjects\SliceContentData;
use Domain\Page\Models\SliceContent;

class UpdateSliceContentAction
{
    public function execute(SliceContent $sliceContent, SliceContentData $sliceContentData): SliceContent
    {
        $sliceContent->update([
            'slice_id' => $sliceContentData->slice_id,
            'data' => $sliceContentData->data,
        ]);

        return $sliceContent;
    }
}
