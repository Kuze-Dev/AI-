<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\CollectionResource\Traits;

trait ColumnOffsets
{
    protected function getMainColumnOffset(): int
    {
        if ( ! empty($this->ownerRecord->taxonomies->toArray()) || $this->ownerRecord->hasPublishDates()) {
            return 8;
        }

        return 12;
    }
}
