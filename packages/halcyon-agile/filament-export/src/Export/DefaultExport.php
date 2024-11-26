<?php

declare(strict_types=1);

namespace HalcyonAgile\FilamentExport\Export;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Queue\SerializesModels;
use Laravel\SerializableClosure\SerializableClosure;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * @template RowType of \Illuminate\Database\Eloquent\Model
 */
readonly class DefaultExport implements FromQuery, ShouldQueue, WithCustomChunkSize, WithHeadings, WithMapping
{
    use SerializesModels;

    /**
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $modelClass
     * @param  array<int, string>  $headings
     * @param  array<int, non-empty-string>  $tags
     */
    public function __construct(
        private string $modelClass,
        private array $headings,
        private SerializableClosure $mapUsing,
        private int $chunkSize,
        private ?SerializableClosure $query = null,
        private ?array $recordIds = null,
        private array $tags = [],
    ) {
    }

    /**
     * @return Builder<RowType>
     *
     * @throws \Laravel\SerializableClosure\Exceptions\PhpVersionNotSupportedException
     */
    public function query(): Builder
    {
        /** @var Builder<RowType> $query */
        $query = app($this->modelClass)->query();

        return $query
            ->when($this->query, fn (Builder $query) => $this->query?->getClosure()($query))
            ->when($this->recordIds, fn (Builder $query) => $query->whereKey($this->recordIds));
    }

    public function headings(): array
    {
        return $this->headings;
    }

    /**
     * @param  RowType  $row
     *
     * @throws \Laravel\SerializableClosure\Exceptions\PhpVersionNotSupportedException
     */
    public function map($row): array
    {
        /** @phpstan-ignore-next-line Method Domain\Excel\Export\DefaultExport::map() should return array but returns Closure. */
        return value($this->mapUsing->getClosure(), $row);
    }

    public function chunkSize(): int
    {
        return $this->chunkSize;
    }

    /**
     * horizon compatible tags
     */
    public function tags(): array
    {
        return $this->tags;
    }
}
