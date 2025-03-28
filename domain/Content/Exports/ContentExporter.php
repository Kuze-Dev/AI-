<?php

declare(strict_types=1);

namespace Domain\Content\Exports;

use App\Jobs\QueueJobPriority;
use Domain\Content\Models\Content;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ContentExporter extends Exporter
{
    protected static ?string $model = Content::class;

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
            ExportColumn::make('prefix'),
            ExportColumn::make('visibility'),
            ExportColumn::make('past_publish_date_behavior')
                ->state(fn (Content $record) => $record->past_publish_date_behavior?->value),
            ExportColumn::make('future_publish_date_behavior')
                ->state(fn (Content $record) => $record->future_publish_date_behavior?->value),
            ExportColumn::make('is_sortable'),
            ExportColumn::make('sites')
                ->state(function (Content $record) {
                    return implode(',', $record->sites->pluck('domain')->toArray());
                }),
            ExportColumn::make('taxonomies')
                ->state(function (Content $record) {
                    return implode(',', $record->taxonomies->pluck('slug')->toArray());
                }),
            ExportColumn::make('created_at')
                ->state(
                    fn (Content $record) => $record->created_at
                        ?->format(Table::$defaultDateTimeDisplayFormat)
                ),
        ];
    }

    #[\Override]
    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your Content export has completed and '.number_format($export->successful_rows).
            ' '.Str::of('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).
                ' '.Str::of('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
