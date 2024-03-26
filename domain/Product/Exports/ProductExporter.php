<?php

declare(strict_types=1);

namespace Domain\Product\Exports;

use Domain\Product\Enums\Status;
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
            ExportColumn::make('id')
                ->label('Product id'),

            ExportColumn::make('is_variant'),
            ExportColumn::make('variant_id'),
            ExportColumn::make('name'),
            ExportColumn::make('variant_combination'),
            ExportColumn::make('sku'),
            ExportColumn::make('retail_price'),
            ExportColumn::make('selling_price'),
            ExportColumn::make('stock'),

            ExportColumn::make('status')
                ->state(
                    fn (Product $record) => $record->status
                    ? Status::ACTIVE->value
                        : Status::INACTIVE->value
                ),

            ExportColumn::make('is_digital_product')
                ->state(
                    fn (Product $record) => $record->is_digital_product
                        ? 'yes'
                        : 'no'
                ),
            ExportColumn::make('is_featured')
                ->state(
                    fn (Product $record) => $record->is_featured
                        ? 'yes'
                        : 'no'
                ),
            ExportColumn::make('is_special_offer')
                ->state(
                    fn (Product $record) => $record->is_special_offer
                        ? 'yes'
                        : 'no'
                ),
            ExportColumn::make('allow_customer_remarks')
                ->state(
                    fn (Product $record) => $record->allow_customer_remarks
                        ? 'yes'
                        : 'no'
                ),
            ExportColumn::make('allow_stocks')
                ->state(
                    fn (Product $record) => $record->allow_stocks
                        ? 'yes'
                        : 'no'
                ),
            ExportColumn::make('allow_guest_purchase')
                ->state(
                    fn (Product $record) => $record->allow_guest_purchase
                        ? 'yes'
                        : 'no'
                ),

            ExportColumn::make('weight'),

            ExportColumn::make('length')
                ->state(
                    fn (Product $record) => $record->dimension['length'] ?? ''
                ),
            ExportColumn::make('width')
                ->state(
                    fn (Product $record) => $record->dimension['width'] ?? ''
                ),
            ExportColumn::make('height')
                ->state(
                    fn (Product $record) => $record->dimension['height'] ?? ''
                ),

            ExportColumn::make('minimum_order_quantity'),
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
