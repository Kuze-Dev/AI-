<?php

declare(strict_types=1);

namespace Domain\Form\Exports;

use App\Jobs\QueueJobPriority;
use Domain\Form\Models\Form;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class FormExporter extends Exporter
{
    protected static ?string $model = Form::class;

    // public function getJobQueue(): ?string
    // {
    //     return QueueJobPriority::DEFAULT;
    // }

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
            ExportColumn::make('store_submission')
                ->label('store_submission'),
            ExportColumn::make('uses_captcha')
                ->label('uses_captcha'),

            ExportColumn::make('sites')
                ->label('sites')
                ->state(function (Form $record) {
                    return implode(',', $record->sites->pluck('domain')->toArray());
                }),

            ExportColumn::make('formEmailNotifications')
                ->label('formEmailNotifications')
                ->state(function (Form $record) {
                    return json_encode($record->formEmailNotifications->toArray());
                }),

            ExportColumn::make('created_at')
                ->label('created_at')
                ->state(
                    fn (Form $record) => $record->created_at
                        ?->format(Table::$defaultDateTimeDisplayFormat)
                ),
        ];
    }

    #[\Override]
    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your Form export has completed and '.number_format($export->successful_rows).
            ' '.Str::of('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).
                ' '.Str::of('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
