<?php

declare(strict_types=1);

namespace Domain\Collection\Models\Builders;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Domain\Collection\Enums\PublishBehavior;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends \Illuminate\Database\Eloquent\Builder<\Domain\Collection\Models\CollectionEntry>
 */
class CollectionEntryBuilder extends Builder
{
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

    public function whereDateRange(array $publishedAtRange, string $timezone = null): self
    {
        $start = CarbonImmutable::parse($publishedAtRange[0])
            ->timezone($timezone)
            ->startOfDay();
        
        $end = CarbonImmutable::parse($publishedAtRange[1])
            ->timezone($timezone)
            ->endOfDay();

        return $this->whereBetween('published_at', [$start, $end]);
    }

    public function whereEntryYear(string|int $publishedYear, string $timezone = null): self 
    {
        $selectedYear = CarbonImmutable::create($publishedYear);
        
        $yearStart = $selectedYear->startOfYear()->startOfDay()->toDateTimeString();
        $yearEnd = $selectedYear->endOfYear()->endOfDay()->toDateTimeString();

        return $this->whereBetween('published_at', [$yearStart, $yearEnd]);
    }

    public function whereEntryMonth(string|int $month): self 
    {   
        return $this->whereMonth('published_at', $month);
    }

    public function whereTaxonomyTerm($taxonomyTerm): self 
    {
        return $this->whereHas(
            'taxonomyTerms',
            function (Builder $query) use ($taxonomyTerm) {
                $query->when(
                    !is_array($taxonomyTerm[key($taxonomyTerm)]),
                    fn ($query) => $query->where('slug', $taxonomyTerm[key($taxonomyTerm)])
                )
                ->when(
                    is_array($taxonomyTerm[key($taxonomyTerm)]),
                    fn ($query) => $query->whereIn('slug', $taxonomyTerm[key($taxonomyTerm)])
                )
                ->whereHas(
                    'taxonomy',
                    fn ($query) => $query->where('slug', key($taxonomyTerm))
                );
            }
        );
    }
}
