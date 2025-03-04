<?php

declare(strict_types=1);

namespace Support\MetaData\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphOne;

interface HasMetaData
{
    /**
     *  @return MorphOne<\Support\MetaData\Models\MetaData>
     *  @phpstan-ignore generics.lessTypes  */
    public function metaData(): MorphOne;

    public function defaultMetaData(): array;
}
