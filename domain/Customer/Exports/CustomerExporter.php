<?php

declare(strict_types=1);

namespace Domain\Customer\Exports;

use App\Jobs\QueueJobPriority;
use Domain\Customer\Models\Customer;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CustomerExporter extends Exporter
{
    protected static ?string $model = Customer::class;

    //    public function getJobQueue(): ?string
    //    {
    //        return QueueJobPriority::EXCEL;
    //    }

    #[\Override]
    public static function getColumns(): array
    {
        return [
            ExportColumn::make('cuid')
                ->label('CUID'),
            ExportColumn::make('email'),
            ExportColumn::make('username'),
            ExportColumn::make('first_name'),
            ExportColumn::make('last_name'),
            ExportColumn::make('mobile'),
            ExportColumn::make('birth_date')
                ->state(
                    fn (Customer $record) => $record->birth_date
                        ?->format(Table::$defaultDateTimeDisplayFormat)
                ),
            ExportColumn::make('gender')
                ->state(
                    fn (Customer $record) => $record->gender->getLabel()
                ),
            ExportColumn::make('status')
                ->state(
                    fn (Customer $record) => $record->status->getLabel()
                ),
            ExportColumn::make('birth_date')
                ->state(
                    fn (Customer $record) => $record->birth_date
                        ?->format(Table::$defaultDateDisplayFormat)
                ),
            ExportColumn::make('tier.name'),
            ExportColumn::make('created_at')
                ->state(
                    fn (Customer $record) => $record->created_at
                        ?->format(Table::$defaultDateTimeDisplayFormat)
                ),
        ];
    }

    #[\Override]
    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your customer export has completed and '.number_format($export->successful_rows).
            ' '.Str::of('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).
                ' '.Str::of('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
