<?php

declare(strict_types=1);

namespace Domain\Page\Actions;

use Domain\Page\DataTransferObjects\SliceContentData;
use Domain\Page\Models\Page;
use Domain\Page\Models\SliceContent;

class CreateSliceContentAction
{
    public function execute(Page $page, SliceContentData $sliceContentData): SliceContent
    {
        return $page->sliceContents()->create([
            'slice_id' => $sliceContentData->slice_id,
            'data' => $sliceContentData->data,
        ]);
    }
}
