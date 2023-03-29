<?php

declare(strict_types=1);

namespace Domain\Page\Actions;

use Domain\Page\DataTransferObjects\SliceData;
use Domain\Page\Models\Slice;
use Illuminate\Http\UploadedFile;

class UpdateSliceAction
{
    public function execute(Slice $slice, SliceData $sliceData): Slice
    {
        $slice->update([
            'name' => $sliceData->name,
            'component' => $sliceData->component,
            'data' => $sliceData->data,
            'is_fixed_content' => $sliceData->is_fixed_content,
        ]);

        if ($sliceData->image instanceof UploadedFile && $imageString = $sliceData->image->get()) {
            $slice->addMediaFromString($imageString)
                ->usingFileName($sliceData->image->getClientOriginalName())
                ->usingName(pathinfo($sliceData->image->getClientOriginalName(), PATHINFO_FILENAME))
                ->toMediaCollection('image');
        }

        if ($sliceData->image === null) {
            $slice->clearMediaCollection('image');
        }

        return $slice;
    }
}
