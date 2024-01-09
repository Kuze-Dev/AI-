<?php

declare(strict_types=1);

namespace Domain\Content\Models\Builders;

use Domain\Content\Enums\PublishBehavior;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * @template TModelClass of \Domain\Content\Models\ContentEntry
 *
 * @extends Builder<TModelClass>
 */
class ContentEntryBuilder extends Builder
{
    /** @return self<\Domain\Content\Models\ContentEntry> */
    public function wherePublishStatus(?PublishBehavior $publishBehavior = null, ?string $timezone = null): self
    {
        return $this->where(
            fn ($query) => $query
                ->where(
                    fn ($query) => $query->where('published_at', '<=', now($timezone)->endOfDay())
                        ->whereRelation('content', 'past_publish_date_behavior', $publishBehavior)
                )
                ->orWhere(
                    fn ($query) => $query->where('published_at', '>', now($timezone)->endOfDay())
                        ->whereRelation('content', 'future_publish_date_behavior', $publishBehavior)
                )
        );
    }

    /** @return self<\Domain\Content\Models\ContentEntry> */
    public function wherePublishedAtRange(?Carbon $publishedAtStart = null, ?Carbon $publishedAtEnd = null): self
    {
        return $this
            ->when(
                $publishedAtStart,
                fn (self $query, $date): self => $query->whereDate('published_at', '>=', $date)
            )
            ->when(
                $publishedAtEnd,
                fn (self $query, $date): self => $query->whereDate('published_at', '<=', $date)
            );
    }

    /** @return self<\Domain\Content\Models\ContentEntry> */
    public function wherePublishedAtYearMonth(int $year, ?int $month = null): self
    {
        $selectedDate = tap(
            now()->year($year),
            fn (Carbon $date) => $month
                ? $date->month($month)
                : $date
        )
            ->toImmutable();

        return blank($month)
            ? $this->whereBetween('published_at', [$selectedDate->startOfYear(), $selectedDate->endOfYear()])
            : $this->whereBetween('published_at', [$selectedDate->startOfMonth(), $selectedDate->endOfMonth()]);
    }

    /** @return self<\Domain\Content\Models\ContentEntry> */
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
