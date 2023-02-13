<?php

declare(strict_types=1);

namespace Domain\Support\MetaTag;

use Domain\Support\MetaTag\Models\MetaTag;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasMetaTags
{
    /** @return \Illuminate\Database\Eloquent\Relations\MorphOne<\Domain\Support\MetaTag\Models\MetaTag> */
    public function metaTags(): MorphOne
    {
        return $this->morphOne(MetaTag::class, 'taggable');
    }
}
