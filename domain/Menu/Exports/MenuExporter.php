<?php

declare(strict_types=1);

namespace Domain\Menu\Exports;

use Domain\Menu\Models\Menu;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class MenuExporter extends Exporter
{
    protected static ?string $model = Menu::class;

    #[\Override]
    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name')
                ->label('name'),
            ExportColumn::make('slug')
                ->label('slug'),
            ExportColumn::make('locale')
                ->label('locale'),
            ExportColumn::make('sites')
                ->label('sites')
                ->state(function (Menu $record) {
                    return implode(',', $record->sites->pluck('domain')->toArray());
                }),
            ExportColumn::make('parent_translation')
                ->label('parent_translation')
                ->state(fn (Menu $record) => $record->parentTranslation?->slug),
            ExportColumn::make('nodes')
                ->label('nodes')
                ->state(fn (Menu $record) => json_encode($record->parentNodes?->load('children')->toArray())),
            ExportColumn::make('created_at')
                ->label('created_at')
                ->state(
                    fn (Menu $record) => $record->created_at
                        ?->format(Table::$defaultDateTimeDisplayFormat)
                ),
        ];
    }

    #[\Override]
    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your Menu export has completed and '.number_format($export->successful_rows).
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
