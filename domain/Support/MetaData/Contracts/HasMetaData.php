<?php

namespace Domain\Support\MetaData\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphOne;

interface HasMetaData
{
    /** @return \Illuminate\Database\Eloquent\Relations\MorphOne<\Domain\Support\MetaData\Models\MetaData> */
    public function metaData(): MorphOne;
    
    public function defaultMetaData(): array;
}
