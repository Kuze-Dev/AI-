<?php

declare(strict_types=1);

namespace HalcyonAgile\FilamentExport\Actions\Concerns;

use Closure;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Notifications\Notification;
use HalcyonAgile\FilamentExport\Events\ExportFinishedEvent;
use HalcyonAgile\FilamentExport\Export\DefaultExport;
use HalcyonAgile\FilamentExport\Helpers;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Laravel\SerializableClosure\SerializableClosure;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

trait ExportsRecords
{
    /** @var class-string|Closure|null */
    protected string|Closure|null $exportClass = null;

    protected string|Closure|null $fileName = null;

    // /** @var array<int, string> */
    protected Closure|array $headings;

    protected Closure $mapUsing;

    protected ?Closure $query = null;

    protected string|array|null $writerType = null;

    protected bool $isQueued = false;

    protected int $chunkSize = 1_000;

    /** @var array<int, non-empty-string> */
    protected array $tags = [];

    /**
     * @param  Closure|array<int, string>  $headings
     *
     * @throws \Laravel\SerializableClosure\Exceptions\PhpVersionNotSupportedException
     */
    public function mapUsing(Closure|array $headings, Closure $mapUsing): self
    {
        $this->headings = $headings;
        $this->mapUsing = $mapUsing;

        return $this;
    }

    protected function getHeading(): array
    {
        return $this->evaluate($this->headings);
    }

    protected function buildExportForm(): array
    {
        if (is_string($this->writerType)) {
            return [];
        }

        $writeTypeOptions = [
            Excel::XLS => 'XLS',
            Excel::XLSX => 'XLSX',
            Excel::CSV => 'CSV',
        ];

        if (is_array($this->writerType)) {
            $writeTypeOptions = array_filter(
                $writeTypeOptions,
                fn (string $key) => in_array($key, $this->writerType),
                ARRAY_FILTER_USE_KEY
            );
        }

        return [
            Forms\Components\Select::make('writer_type')
                ->label(trans('Type'))
                ->options($writeTypeOptions)
                ->required()
                ->in(array_keys($writeTypeOptions)),
        ];
    }

    public function writerType(string|array $writerType): self
    {
        if (count($invalidWriterTypes = array_diff(Arr::wrap($writerType), [Excel::XLS, Excel::XLSX, Excel::CSV])) > 0) {
            throw new InvalidArgumentException('The following writer types are not supported: '.implode(', ', $invalidWriterTypes));
        }

        $this->writerType = $writerType;

        return $this;
    }

    /** @throws \Laravel\SerializableClosure\Exceptions\PhpVersionNotSupportedException */
    public function query(Closure $query): self
    {
        $this->query = $query;

        return $this;
    }

    /** @param  class-string|Closure  $exportClass */
    public function exportClass(string|Closure $exportClass): self
    {
        $this->exportClass = $exportClass;

        return $this;
    }

    /** @throws \Laravel\SerializableClosure\Exceptions\PhpVersionNotSupportedException */
    protected function getExportClass(): object
    {
        $exportClass = $this->evaluate($this->exportClass);

        if (is_object($exportClass)) {
            return $exportClass;
        }

        if (is_string($exportClass) && class_exists($exportClass)) {
            return new $exportClass();
        }

        /** @var class-string<\Illuminate\Database\Eloquent\Model>|null $model */
        $model = $this->getModel();

        if (! $model) {
            throw new InvalidArgumentException('No model provided.');
        }

        $recordIds = method_exists($this, 'getRecords')
            ? $this->getRecords()?->modelKeys()
            : null;

        return new DefaultExport(
            modelClass: $model,
            headings: $this->getHeading(),
            mapUsing: new SerializableClosure($this->mapUsing),
            chunkSize: $this->chunkSize,
            query: $this->query ? new SerializableClosure($this->query) : null,
            recordIds: $recordIds,
            tags: $this->tags
        );
    }

    /** @throws \Laravel\SerializableClosure\Exceptions\PhpVersionNotSupportedException */
    protected function processExport(string $writerType): ?BinaryFileResponse
    {
        $fileName = $this->getFileName($writerType);
        $exportClass = $this->getExportClass();

        if (! $this->isQueued) {
            return ExcelFacade::download($exportClass, $fileName, $writerType);
        }

        /** @var \Illuminate\Database\Eloquent\Model $user */
        $user = Filament::auth()->user();

        ExcelFacade::queue(
            export: $exportClass,
            filePath: Helpers::fullPath($fileName),
            disk: config('filament-export.temporary_files.disk'),
            writerType: $writerType,
        )
            ->chain([fn () => event(new ExportFinishedEvent($user, $fileName))]);

        Notification::make()
            ->title(trans('Export queued'))
            ->body(trans('The export was queued. You will be notified when it is ready for download.'))
            ->success()
            ->icon('heroicon-o-inbox-in')
            ->send();

        return null;
    }

    public function queue(): static
    {
        $this->isQueued = true;

        return $this;
    }

    public function withChunkSize(int $chunkSize): static
    {
        $this->chunkSize = $chunkSize;

        return $this;
    }

    public function fileName(string|Closure $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * @param  array<int, non-empty-string>  $tags
     */
    public function tags(array $tags): static
    {
        $this->tags = $tags;

        return $this;
    }

    public function getFileName(string $writerType): string
    {
        $fileName = $this->evaluate($this->fileName, ['writerType' => $writerType]);

        if ($fileName) {
            return $fileName;
        }

        /** @phpstan-ignore-next-line */
        $modelLabel = Str::kebab($this->getPluralModelLabel());
        $dateTime = Helpers::now()->toDateTimeString();
        $fileExtension = Str::lower($writerType);

        return $modelLabel.'-'.$dateTime.'.'.$fileExtension;
    }
}
