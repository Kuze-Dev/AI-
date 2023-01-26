<?php

declare(strict_types=1);

namespace Domain\Page\Actions;

use Domain\Page\Models\SliceContent;

class DeleteSliceContentAction
{
    public function execute(SliceContent $sliceContent): ?bool
    {
        return $sliceContent->delete();
    }
}
