<?php

declare(strict_types=1);

namespace Domain\Product\Exports;

use Domain\Product\Models\Product;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property-read Product $record
 */
class ProductExporter extends Exporter
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name'),
        ];
    }

    public static function modifyQuery(Builder $query): Builder
    {
        return $query->with('productVariants')->latest();
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your product export has completed and '.
            number_format($export->successful_rows).' '.
            str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.
                str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
