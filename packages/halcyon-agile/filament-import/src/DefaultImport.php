<?php

declare(strict_types=1);

namespace HalcyonAgile\FilamentImport;

use HalcyonAgile\FilamentImport\Listeners\SendImportFailedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\SerializableClosure\SerializableClosure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Events\ImportFailed;

readonly class DefaultImport implements ShouldQueue, ToModel, WithBatchInserts, WithChunkReading, WithEvents, WithHeadingRow, WithUpserts, WithValidation
{
    /**
     * @param  non-empty-string  $uniqueBy
     * @param  non-negative-int  $batchSize
     * @param  non-negative-int  $chunkSize
     * @param  array<int, non-empty-string>  $tags
     */
    public function __construct(
        private Authenticatable $user,
        private SerializableClosure $processRowsUsing,
        private string $uniqueBy,
        private array $validateRules,
        private int $batchSize,
        private int $chunkSize,
        private array $validateMessages = [],
        private array $validateAttributes = [],
        private array $tags = [],
    ) {}

    public function uniqueBy(): string
    {
        return $this->uniqueBy;
    }

    public function batchSize(): int
    {
        return $this->batchSize;
    }

    public function chunkSize(): int
    {
        return $this->chunkSize;
    }

    /** @throws \Laravel\SerializableClosure\Exceptions\PhpVersionNotSupportedException */
    public function model(array $row)
    {
        return $this->processRowsUsing->getClosure()($row);
    }

    public function rules(): array
    {
        return $this->validateRules;
    }

    public function customValidationMessages(): array
    {
        return $this->validateMessages;
    }

    public function customValidationAttributes(): array
    {
        return $this->validateAttributes;
    }

    public function registerEvents(): array
    {
        return [
            ImportFailed::class => new SendImportFailedNotification($this->user),
        ];
    }

    /**
     * horizon compatible tags
     */
    public function tags(): array
    {
        return $this->tags;
    }
}
