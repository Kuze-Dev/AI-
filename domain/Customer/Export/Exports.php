<?php

declare(strict_types=1);

namespace Domain\Customer\Export;

use Domain\Customer\Models\Customer;
use HalcyonAgile\FilamentExport\ExcelExport;
use Illuminate\Database\Eloquent\Builder;
use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Columns\Column;

final class Exports
{
    private function __construct()
    {
    }

    private static function export(): array
    {
        return [
            ExcelExport::make()
                ->askForFilename()
                ->askForWriterType()
                ->queue()
                ->withChunkSize(500)
                ->modifyQueryUsing(
                    fn (Builder $query) =>
                    /** @var Builder|Customer $query */
                    $query
                        ->with('tier')
                        ->latest()
                )
                ->withColumns([
                    Column::make('cuid'),
                    Column::make('email'),
                    Column::make('first_name'),
                    Column::make('last_name'),
                    Column::make('mobile'),
                    Column::make('status')
                        ->formatStateUsing(
                            fn (Customer $record) => $record->status?->value ?? 'none'
                        ),
                    Column::make('birth_date')
                        ->formatStateUsing(
                            fn (Customer $record) => $record->birth_date
                                ?->format(config('tables.date_time_format'))
                        ),
                    Column::make('tier.name'),
                    Column::make('created_at')
                        ->formatStateUsing(
                            fn (Customer $record) => $record->created_at
                                ?->format(config('tables.date_time_format'))
                        ),
                ]),
        ];
    }

    public static function tableBulk(): ExportBulkAction
    {
        return ExportBulkAction::make()->exports(self::export());
    }

    public static function headerList(): ExportAction
    {
        return ExportAction::make()->exports(self::export());
    }
}
