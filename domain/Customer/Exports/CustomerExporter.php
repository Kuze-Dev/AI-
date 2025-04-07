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
                ->label('cuid'),
            ExportColumn::make('email')
                ->label('email'),
            ExportColumn::make('username')
                ->label('username'),
            ExportColumn::make('first_name')
                ->label('first_name'),
            ExportColumn::make('last_name')
                ->label('last_name'),
            ExportColumn::make('mobile')
                ->label('mobile'),
            ExportColumn::make('birth_date')
                ->label('birth_date')
                ->state(
                    fn (Customer $record) => $record->birth_date
                        ?->format('m/d/Y')
                ),
            ExportColumn::make('gender')
                ->label('gender')
                ->state(
                    fn (Customer $record) => $record->gender?->value
                ),
            ExportColumn::make('status')
                ->state(
                    fn (Customer $record) => $record->status?->value
                ),
            ExportColumn::make('tier.name')
                ->label('tier'),
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
