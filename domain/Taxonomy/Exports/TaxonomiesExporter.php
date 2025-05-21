<?php

declare(strict_types=1);

namespace Domain\Taxonomy\Exports;

use Domain\Taxonomy\Models\Taxonomy;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class TaxonomiesExporter extends Exporter
{
    protected static ?string $model = Taxonomy::class;

    #[\Override]
    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name')
                ->label('name'),
            ExportColumn::make('blueprint_id')
                ->label('blueprint_id'),
            ExportColumn::make('slug')
                ->label('slug'),
            ExportColumn::make('locale')
                ->label('locale'),
            ExportColumn::make('has_route')
                ->label('has_route'),
            ExportColumn::make('is_custom')
                ->label('is_custom')
                ->state(fn (Taxonomy $record) => $record->routeUrls?->is_override),
            ExportColumn::make('url')
                ->label('url')
                ->state(fn (Taxonomy $record) => $record->routeUrls?->url),
            ExportColumn::make('sites')
                ->label('sites')
                ->state(function (Taxonomy $record) {
                    return implode(',', $record->sites->pluck('domain')->toArray());
                }),
            ExportColumn::make('parent_translation')
                ->label('parent_translation')
                ->state(fn (Taxonomy $record) => $record->parentTranslation?->slug),
            ExportColumn::make('created_at')
                ->label('created_at')
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

    #[\Override]
    public function getFormats(): array
    {
        return [ExportFormat::Csv];
    }
}
