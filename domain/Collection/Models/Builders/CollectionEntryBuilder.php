<?php

declare(strict_types=1);

namespace Domain\Collection\Models\Builders;

use Carbon\Carbon;
use Domain\Collection\Enums\PublishBehavior;
use Illuminate\Database\Eloquent\Builder;

/**
 * @template TModelClass of \Domain\Collection\Models\CollectionEntry
 * @extends Builder<TModelClass>
 */
class CollectionEntryBuilder extends Builder
{
    /** @return self<\Domain\Collection\Models\CollectionEntry> */
    public function wherePublishStatus(PublishBehavior $publishBehavior = null, string $timezone = null): self
    {
        return $this->where(
            fn ($query) => $query
                ->where(
                    fn ($query) => $query->where('published_at', '<=', now($timezone)->endOfDay())
                        ->whereRelation('collection', 'past_publish_date_behavior', $publishBehavior)
                )
                ->orWhere(
                    fn ($query) => $query->where('published_at', '>', now($timezone)->endOfDay())
                        ->whereRelation('collection', 'future_publish_date_behavior', $publishBehavior)
                )
        );
    }

    /** @return self<\Domain\Collection\Models\CollectionEntry> */
    public function wherePublishedAtRange(Carbon $publishedAtFrom = null, Carbon $publishedAtEnd = null): self
    {
        return $this
            ->when(
                $publishedAtFrom,
                fn (self $query, $date): self => $query->whereDate('published_at', '>=', $date)
            )
            ->when(
                $publishedAtEnd,
                fn (self $query, $date): self => $query->whereDate('published_at', '<=', $date)
            );
    }

    /** @return self<\Domain\Collection\Models\CollectionEntry> */
    public function wherePublishedAtYearMonth(int $year, int $month = null, string $timezone = null): self
    {
        $selectedDate = tap(
            Carbon::now()->year($year),
            fn (Carbon $date) => $month
                ? $date->month($month)
                : $date
        )
            ->toImmutable()
            ->timezone($timezone ?? config('app.timezone', 'UTC'));

        return blank($month)
            ? $this->whereBetween('published_at', [$selectedDate->startOfYear(), $selectedDate->endOfYear()])
            : $this->whereBetween('published_at', [$selectedDate->startOfMonth(), $selectedDate->endOfMonth()]);
    }

    /** @return self<\Domain\Collection\Models\CollectionEntry> */
    public function whereTaxonomyTerms(string $taxonomy, array $terms): self
    {
        return $this->whereHas(
            'taxonomyTerms',
            function (Builder $query) use ($taxonomy, $terms) {
                $query->whereIn('slug', $terms)
                    ->whereHas(
                        'taxonomy',
                        fn ($query) => $query->where('slug', $taxonomy)
                    );
            }
        );
    }
}
