<?php

declare(strict_types=1);

namespace Support\Excel\Import;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Laravel\SerializableClosure\SerializableClosure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Events\ImportFailed;
use Support\Excel\Listeners\SendImportFailedNotification;

class DefaultImport implements ShouldQueue, ToModel, WithBatchInserts, WithChunkReading, WithEvents, WithHeadingRow, WithUpserts, WithValidation
{
    public function __construct(
        private readonly Model $user,
        private readonly SerializableClosure $processRowsUsing,
        private readonly string $uniqueBy,
        private readonly array $validateRules,
        private readonly array $validateMessages = [],
        private readonly array $validateAttributes = [],
        private readonly int $batchSize = 1_000,
    ) {
    }

    public function batchSize(): int
    {
        return $this->batchSize;
    }

    public function chunkSize(): int
    {
        return 100;
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

    public function uniqueBy(): string
    {
        return $this->uniqueBy;
    }
}
