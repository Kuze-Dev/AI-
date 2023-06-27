<?php

declare(strict_types=1);

namespace Support\Excel\Import;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Laravel\SerializableClosure\SerializableClosure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Events\ImportFailed;
use Support\Excel\Notifications\ImportFailed as ImportFailedNotification;

class DefaultImport implements ShouldQueue, ToModel, WithValidation, WithChunkReading, WithHeadingRow, WithEvents
{
    public function __construct(
        private readonly Model $user,
        private readonly SerializableClosure $processRowsUsing,
        private readonly array $validateRules,
        private readonly array $validateMessages = [],
        private readonly array $validateAttributes = []
    ) {
    }

    public function chunkSize(): int
    {
        return 500;
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
        return [ImportFailed::class => function (ImportFailed $event) {
            if ($event->getException() instanceof ValidationException) {
                Notification::send(
                    $this->user,
                    new ImportFailedNotification($event->getException()->errors()[0][0])
                );
            }
        },
        ];
    }
}
