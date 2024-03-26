<?php

declare(strict_types=1);

namespace Domain\Product\Imports;

use Domain\Product\Models\Product;
use Domain\Product\Models\ProductVariant;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Validation\Rule;

/**
 * @property-read ProductVariant $record
 */
class ProductVariantImporter extends Importer
{
    protected static ?string $model = ProductVariant::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('product')
                ->requiredMapping()
                ->relationship(resolveUsing: ['slug'])
                ->rules(['required', Rule::exists(Product::class, 'slug')])
                ->exampleHeader('Product slug')
                ->example('slug')
                ->guess(['Product slug']),

            ImportColumn::make('sku')
                ->requiredMapping()
                ->rules(['required', Rule::exists(ProductVariant::class, 'slug')])
                ->exampleHeader('SKU')
                ->example('slug'),

            ImportColumn::make('stock')
                ->requiredMapping()
                ->rules(['required', 'numeric', 'min:0'])
                ->exampleHeader('Stock')
                ->example('123'),

            ImportColumn::make('retail_price')
                ->requiredMapping()
                ->rules(['required', 'numeric', 'min:0.1'])
                ->exampleHeader('Retail price')
                ->example('123.44'),

            ImportColumn::make('selling_price')
                ->requiredMapping()
                ->rules(['required', 'numeric', 'min:0.1'])
                ->exampleHeader('Selling price')
                ->example('123.44'),

            ImportColumn::make('product_option_1_name')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:100'])
                ->exampleHeader('Product option 1 name')
                ->example('test'),

            ImportColumn::make('product_option_1_value_1')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:100'])
                ->exampleHeader('Product option 1 value 1')
                ->example('test'),

            ImportColumn::make('product_option_1_is_custom')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:100'])
                ->exampleHeader('Product option 1 is custom')
                ->example('test'),
        ];
    }

    public function resolveRecord(): ?ProductVariant
    {
        return ProductVariant::query()
            ->where('sku', $this->data['sku'])
            ->first();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your product import has completed and '.number_format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
