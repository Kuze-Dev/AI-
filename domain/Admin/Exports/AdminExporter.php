<?php

declare(strict_types=1);

namespace Domain\Admin\Exports;

use Domain\Admin\Models\Admin;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class AdminExporter extends Exporter
{
    protected static ?string $model = Admin::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('email'),
            ExportColumn::make('first_name'),
            ExportColumn::make('last_name'),
            ExportColumn::make('active')
                ->state(
                    fn (Admin $record): string => $record->active ? 'yes' : 'no'
                ),
            ExportColumn::make('roles')
                ->state(
                    fn (Admin $record): string => $record->getRoleNames()->implode(', ')
                ),
            ExportColumn::make('created_at')
                ->state(
                    fn (Admin $record) => $record->created_at
                        ?->format(Table::$defaultDateTimeDisplayFormat)
                ),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your admin export has completed and '.number_format($export->successful_rows).
            ' '.Str::of('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).
                ' '.Str::of('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
