<?php

declare(strict_types=1);

namespace Support\MetaData;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Support\MetaData\Models\MetaData;

trait HasMetaData
{
    /** @return MorphOne<MetaData> */
    public function metaData(): MorphOne
    {
        return $this->morphOne(MetaData::class, 'model');
    }
}
