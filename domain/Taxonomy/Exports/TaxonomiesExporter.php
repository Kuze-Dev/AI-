<?php

declare(strict_types=1);

namespace Domain\Taxonomy\Exports;

use App\Jobs\QueueJobPriority;
use Domain\Taxonomy\Models\Taxonomy;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class TaxonomiesExporter extends Exporter
{
    protected static ?string $model = Taxonomy::class;

    public function getJobQueue(): ?string
    {
        return QueueJobPriority::DEFAULT;
    }

    #[\Override]
    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name'),
            ExportColumn::make('blueprint_id'),
            ExportColumn::make('slug'),
            ExportColumn::make('locale'),
            ExportColumn::make('has_route'),
            ExportColumn::make('parent_translation')
                ->state(fn (Taxonomy $record) => $record->parentTranslation?->slug),
            ExportColumn::make('created_at')
                ->state(
                    fn (Taxonomy $record) => $record->created_at
                        ?->format(Table::$defaultDateTimeDisplayFormat)
                ),
        ];
    }

    #[\Override]
    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your Taxonomy export has completed and '.number_format($export->successful_rows).
            ' '.Str::of('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).
                ' '.Str::of('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }

    // public static function modifyQuery(Builder $query): Builder
    // {
    //     dd($query);
    //     return Taxonomy::query()->with('dataTranslation');
    // }
}
