<?php

declare(strict_types=1);

namespace Domain\Collection\Models\Builders;

use Domain\Collection\Enums\PublishBehavior;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends \Illuminate\Database\Eloquent\Builder<\Domain\Collection\Models\CollectionEntry>
 */
class CollectionEntryBuilder extends Builder
{
    public function wherePublishStatus(PublishBehavior $publishBehavior = null, string $timezone = null): self
    {
        return $this
            ->where(
                fn ($query) => $query->where('published_at', '>', now($timezone))
                    ->whereHas(
                        'collection',
                        fn ($query) => $query->where('future_publish_date_behavior', $publishBehavior)
                    )
            )
            ->orWhere(
                fn ($query) => $query->where('published_at', '<=', now($timezone))
                    ->whereHas(
                        'collection',
                        fn ($query) => $query->where('past_publish_date_behavior', $publishBehavior)
                    )
            );
    }
}
