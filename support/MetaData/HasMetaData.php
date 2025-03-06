<?php

declare(strict_types=1);

namespace Support\MetaData;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Support\MetaData\Models\MetaData;

trait HasMetaData
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne<\Support\MetaData\Models\MetaData, $this>
     *
     *  @phpstan-ignore method.childReturnType, method.childReturnType, method.childReturnType, method.childReturnType
     */
    public function metaData(): MorphOne
    {
        return $this->morphOne(MetaData::class, 'model');
    }
}
