<?php

declare(strict_types=1);

namespace Support\Excel\Actions;

use Closure;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Actions\Action;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Str;
use Laravel\SerializableClosure\SerializableClosure;
use Maatwebsite\Excel\Facades\Excel;
use Support\Excel\Events\ImportFinished;
use Support\Excel\Import\DefaultImport;

class ImportAction extends Action
{
    /** @var class-string|Closure|null */
    protected string|Closure|null $importClass = null;

    protected Closure $processRowsUsing;

    protected array $validateRules;

    protected array $validateMessages;

    protected array $validateAttributes;

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
                    ->disk(config('support.excel.temporary_files.disk'))
                    ->directory(Str::finish(config('support.excel.temporary_files.base_directory'), '/imports/')),
            ])
            ->withActivityLog(
                event: 'imported',
                description: fn (self $action) => 'Imported '.$action->getModelLabel()
            );
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

        /** @var \Illuminate\Database\Eloquent\Model $user */
        $user = Filament::auth()->user();

        return new DefaultImport(
            user: $user,
            processRowsUsing: new SerializableClosure($this->processRowsUsing),
            validateRules: $this->validateRules,
            validateMessages: $this->validateMessages,
            validateAttributes: $this->validateAttributes
        );
    }
}
