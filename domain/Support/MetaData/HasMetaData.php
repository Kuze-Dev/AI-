<?php

declare(strict_types=1);

namespace Domain\Support\MetaData;

use Domain\Support\MetaData\Models\MetaData;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasMetaData
{
    /** @return \Illuminate\Database\Eloquent\Relations\MorphOne<\Domain\Support\MetaData\Models\MetaData> */
    public function metaTags(): MorphOne
    {
        return $this->morphOne(MetaData::class, 'taggable');
    }
}
