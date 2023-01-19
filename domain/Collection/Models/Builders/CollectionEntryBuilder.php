<?php

namespace Domain\Collection\Models\Builders;

use Auth;
use Carbon\Carbon;
use Domain\Collection\Enums\PublishBehavior;
use Domain\Collection\Models\Collection;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends \Illuminate\Database\Eloquent\Builder<\Domain\Collection\Models\CollectionEntry>
 */
class CollectionEntryBuilder extends Builder
{
    public function wherePublishStatus(PublishBehavior $publishBehavior, Collection $collection): self
    {
        $recordedDates = $collection->collectionEntries()->pluck('published_at');
        $timezone = Auth::user()->timezone ?? 'Asia/Manila';

        if ($collection->past_publish_date_behavior == $publishBehavior) {
            $minDate = Carbon::parse($recordedDates->min())->timezone($timezone)->startOfDay();
            $currentDate = Carbon::now()->timezone($timezone)->startOfDay();

            return $this->whereBetween('published_at', [$minDate, $currentDate]);
        }

        if ($collection->future_publish_date_behavior == $publishBehavior) {
            $maxDate = Carbon::parse($recordedDates->max())->timezone($timezone)->endOfDay();
            $currentDate = Carbon::now()->addDay()->timezone($timezone)->startOfDay();

            return $this->whereBetween('published_at', [$maxDate, $currentDate]);
        }

        return $this->where('id','null');
    }
}
