<?php

declare(strict_types=1);

namespace Domain\Page\Models\Builders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * @template TModelClass of \Domain\Page\Models\Page
 *
 * @extends Builder<TModelClass>
 */
class PageBuilder extends Builder
{
    /** @return self<\Domain\Page\Models\Page> */
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

    /** @return self<\Domain\Page\Models\Page> */
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
}
