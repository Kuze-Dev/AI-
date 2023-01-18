<?php

namespace Domain\Collection\Models\Builders;

use Domain\Collection\Enums\PublishBehavior;
use Illuminate\Database\Eloquent\Builder;

class CollectionEntryBuilder extends Builder
{
    public function wherePusblishStatus(PublishBehavior $publishBehavior): self
    {
        // Add query here
    }
}
