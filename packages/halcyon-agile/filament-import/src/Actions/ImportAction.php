<?php

declare(strict_types=1);

namespace HalcyonAgile\FilamentImport\Actions;

use Closure;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Notifications\Notification;
use HalcyonAgile\FilamentImport\DefaultImport;
use HalcyonAgile\FilamentImport\Events\ImportFinished;
use Illuminate\Foundation\Bus\PendingDispatch;
use Laravel\SerializableClosure\SerializableClosure;
use Maatwebsite\Excel\Facades\Excel;

class ImportAction extends Action
{
    /** @var class-string|Closure|null */
    protected string|Closure|null $importClass = null;

    protected Closure $processRowsUsing;

    protected array $validateRules;

    protected array $validateMessages;

    protected array $validateAttributes;

    /**
     * @var non-empty-string
     */
    protected string $uniqueBy;

    /**
     * @var non-negative-int
     */
    protected int $batchSize = 1_000;

    /**
     * @var non-negative-int
     */
    protected int $chunkSize = 5_00;

    /**
     * @var array<int, non-empty-string>
     */
    protected array $tags = [];

    public static function getDefaultName(): ?string
    {
        return 'import';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->translateLabel()
            ->action(function (array $data) {
                /** @var \Maatwebsite\Excel\Excel|PendingDispatch */
                $response = Excel::import($this->getImportClass(), $data['file']);

                if ($response instanceof PendingDispatch) {
                    /** @var \Illuminate\Database\Eloquent\Model $user */
                    $user = Filament::auth()->user();

                    $response->chain([fn () => event(new ImportFinished($user))]);

                    Notification::make()
                        ->title(trans('Import queued'))
                        ->body(trans('The import was queued. You will be notified when it is finished.'))
                        ->icon('heroicon-o-upload')
                        ->success()
                        ->send();

                    return;
                }

                Notification::make()
                    ->success()
                    ->title(trans('Successfully imported'))
                    ->icon('heroicon-o-check')
                    ->send();
            })
            ->icon('heroicon-o-upload')
            ->form([
                Forms\Components\FileUpload::make('file')
                    ->translateLabel()
                    ->required()
                    // https://stackoverflow.com/q/974079/9311071
                    ->acceptedFileTypes([
                        'application/vnd.ms-excel',
                        'application/msexcel',
                        'application/x-msexcel',
                        'application/x-ms-excel',
                        'application/x-excel',
                        'application/x-dos_ms_excel',
                        'application/xls',
                        'application/x-xls',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // xlsx
                        'text/csv',
                    ])
                    ->disk(config('filament-import.temporary_files.disk'))
                    ->directory(config('filament-import.temporary_files.base_directory')),
            ]);
        //            ->withActivityLog(
        //                event: 'imported',
        //                description: fn (self $action) => 'Imported '.$action->getModelLabel()
        //            );
    }

    /**
     * @param  non-empty-string  $uniqueBy
     */
    public function uniqueBy(string $uniqueBy): self
    {
        $this->uniqueBy = $uniqueBy;

        return $this;
    }

    /**
     * @param  non-negative-int  $batchSize
     */
    public function batchSize(int $batchSize): self
    {
        $this->batchSize = $batchSize;

        return $this;
    }

    /**
     * @param  non-negative-int  $chunkSize
     */
    public function chunkSize(int $chunkSize): self
    {
        $this->chunkSize = $chunkSize;

        return $this;
    }

    /**
     * @param  array<int, non-empty-string>  $tags
     */
    public function tags(array $tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    public function processRowsUsing(Closure $processRowsUsing): self
    {
        $this->processRowsUsing = $processRowsUsing;

        return $this;
    }

    public function withValidation(array $rules, array $messages = [], array $attributes = []): self
    {
        $this->validateRules = $rules;
        $this->validateMessages = $messages;
        $this->validateAttributes = $attributes;

        return $this;
    }

    /** @param  class-string|Closure  $importClass */
    public function importClass(string|Closure $importClass): self
    {
        $this->importClass = $importClass;

        return $this;
    }

    /** @throws \Laravel\SerializableClosure\Exceptions\PhpVersionNotSupportedException */
    protected function getImportClass(): object
    {
        $importClass = $this->evaluate($this->importClass);

        if (is_object($importClass)) {
            return $importClass;
        }

        if (is_string($importClass) && class_exists($importClass)) {
            return new $importClass();
        }

        /** @var \Illuminate\Foundation\Auth\User $user */
        $user = Filament::auth()->user();

        return new DefaultImport(
            user: $user,
            processRowsUsing: new SerializableClosure($this->processRowsUsing),
            uniqueBy: $this->uniqueBy,
            validateRules: $this->validateRules,
            batchSize: $this->batchSize,
            chunkSize: $this->batchSize,
            validateMessages: $this->validateMessages,
            validateAttributes: $this->validateAttributes,
            tags: $this->tags,
        );
    }
}
