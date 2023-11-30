<?php

declare(strict_types=1);

namespace Support\Excel\Export;

use Illuminate\Database\Eloquent\Builder;
use Laravel\SerializableClosure\SerializableClosure;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use romanzipp\QueueMonitor\Traits\IsMonitored;

/**
 * @template TModelClass of \Illuminate\Database\Eloquent\Model
 *
 * @phpstan-ignore-next-line
 */
class DefaultExport implements FromQuery, WithCustomChunkSize, WithHeadings, WithMapping
{
    use IsMonitored;

    /**
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $modelClass
     * @param  array<int, string>  $headings
     */
    public function __construct(
        private readonly string $modelClass,
        private readonly array $headings,
        private readonly SerializableClosure $mapUsing,
        private readonly int $chunkSize = 100,
        private readonly ?SerializableClosure $query = null,
        private readonly ?array $recordIds = null,
    ) {
    }

    /**
     * @return Builder<TModelClass>
     *
     * @throws \Laravel\SerializableClosure\Exceptions\PhpVersionNotSupportedException
     */
    public function query(): Builder
    {
        /** @var Builder<TModelClass> $query */
        $query = app($this->modelClass)->query();

        return $query
            ->when($this->query, fn (Builder $query) => $this->query?->getClosure()($query))
            ->when($this->recordIds, fn (Builder $query) => $query->whereKey($this->recordIds));
    }

    public function headings(): array
    {
        return $this->headings;
    }

    /** @throws \Laravel\SerializableClosure\Exceptions\PhpVersionNotSupportedException */
    public function map($row): array
    {
        /** @phpstan-ignore-next-line Method Domain\Excel\Export\DefaultExport::map() should return array but returns Closure. */
        return value($this->mapUsing->getClosure(), $row);
    }

    public function chunkSize(): int
    {
        return $this->chunkSize;
    }

    public function tags(): array
    {
        return [
            'tenant:'.(tenant('id') ?? 'central'),
        ];
    }
}
