<?php

declare(strict_types=1);

namespace Domain\Support\MetaData\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphOne;

interface HasMetaData
{
    /** @return MorphOne<\Domain\Support\MetaData\Models\MetaData> */
    public function metaData(): MorphOne;

    public function defaultMetaData(): array;
}
