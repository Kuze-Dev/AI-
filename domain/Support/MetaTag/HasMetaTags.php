<?php

declare(strict_types=1);

namespace Domain\Support\MetaTag;

use Domain\Support\MetaTag\Models\MetaTag;

trait HasMetaTags 
{
    public function metaTags()
    {
        return $this->morphMany(MetaTag::class, 'taggable');
    }
}