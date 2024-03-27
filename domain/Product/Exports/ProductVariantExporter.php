<?php

declare(strict_types=1);

namespace Domain\Product\Exports;

use Domain\Product\Enums\Status;
use Domain\Product\Models\ProductVariant;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property-read ProductVariant $record
 */
class ProductVariantExporter extends Exporter
{
    protected static ?string $model = ProductVariant::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('product_id'),
            ExportColumn::make('id')
                ->label('Variant id'),

            ExportColumn::make('combination')
                ->listAsJson(),
            ExportColumn::make('sku'),
            ExportColumn::make('retail_price'),
            ExportColumn::make('selling_price'),
            ExportColumn::make('stock'),

            ExportColumn::make('status')
                ->state(
                    fn (ProductVariant $record) => $record->status
                    ? Status::ACTIVE->value
                        : Status::INACTIVE->value
                ),
        ];
    }

    public static function modifyQuery(Builder $query): Builder
    {
        return ProductVariant::query();
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your product variant export has completed and '.
            number_format($export->successful_rows).' '.
            str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.
                str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
