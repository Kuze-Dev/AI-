<?php

declare(strict_types=1);

namespace Domain\Content\Exports;

use Domain\Content\Models\ContentEntry;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ContentEntriesExporter extends Exporter
{
    protected static ?string $model = ContentEntry::class;

    #[\Override]
    public static function getColumns(): array
    {
        return [
            ExportColumn::make('content')
                ->state(fn (ContentEntry $record) => $record->content->slug)
                ->label('content'),
            ExportColumn::make('title')
                ->label('title'),
            ExportColumn::make('route_url')
                ->state(fn (ContentEntry $record) => $record->activeRouteUrl?->url)
                ->label('route_url'),
            ExportColumn::make('published_at')
                ->state(fn (ContentEntry $record) => $record->published_at?->format('m/d/Y'))
                ->label('published_at'),
            ExportColumn::make('data')
                ->state(fn (ContentEntry $record) => json_encode($record->data))
                ->label('data'),
            ExportColumn::make('status')
                ->label('status'),

            ExportColumn::make('sites')
                ->label('sites')
                ->state(function (ContentEntry $record) {
                    return implode(',', $record->sites->pluck('domain')->toArray());
                }),
            ExportColumn::make('locale')
                ->label('locale'),
            ExportColumn::make('taxonomy_terms')
                ->label('taxonomy_terms')
                ->state(function (ContentEntry $record) {
                    return implode(',', $record->taxonomyTerms->pluck('slug')->toArray());
                }),
            ExportColumn::make('created_at')
                ->label('created_at')
                ->state(
                    fn (ContentEntry $record) => $record->created_at
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

    #[\Override]
    public function getFormats(): array
    {
        return [ExportFormat::Csv];
    }
}
