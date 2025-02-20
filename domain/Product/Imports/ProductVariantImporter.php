<?php

declare(strict_types=1);

namespace Domain\Product\Imports;

use Domain\Product\Models\Product;
use Domain\Product\Models\ProductOption;
use Domain\Product\Models\ProductOptionValue;
use Domain\Product\Models\ProductVariant;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * @property-read ProductVariant $record
 */
class ProductVariantImporter extends Importer
{
    protected static ?string $model = ProductVariant::class;

    #[\Override]
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
                ->rules(['required', 'string', 'max:100'])
                ->exampleHeader('SKU')
                ->example('slug'),

            ImportColumn::make('stock')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'numeric', 'min:0'])
                ->exampleHeader('Stock')
                ->example('1'),

            ImportColumn::make('retail_price')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'numeric', 'min:0.1'])
                ->exampleHeader('Retail price')
                ->example('1.1'),

            ImportColumn::make('selling_price')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'numeric', 'min:0.1'])
                ->exampleHeader('Selling price')
                ->example('1.1'),

            // option 1

            ImportColumn::make('product_option_1_name')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:100'])
                ->exampleHeader('Product option 1 name')
                ->example('test 1'),

            ImportColumn::make('product_option_1_value_1')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:100'])
                ->exampleHeader('Product option 1 value 1')
                ->example('test 1'),

            ImportColumn::make('product_option_1_is_custom')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:100'])
                ->exampleHeader('Product option 1 is custom')
                ->example('test 1'),

            ImportColumn::make('product_option_1_value_1_icon_type')
                ->rules(['nullable', 'string', 'max:100'])
                ->exampleHeader('Product option 1 value 1 icon type')
                ->example('test 1'),

            ImportColumn::make('product_option_1_value_1_icon_value')
                ->rules(['nullable', 'string', 'max:100'])
                ->exampleHeader('Product option 1 value 1 icon value')
                ->example('test 1'),

            // option 2

            ImportColumn::make('product_option_2_name')
                ->rules(['nullable', 'string', 'max:100'])
                ->exampleHeader('Product option 2 name')
                ->example('test 2'),

            ImportColumn::make('product_option_2_value_1')
                ->rules(['nullable', 'string', 'max:100'])
                ->exampleHeader('Product option 2 value 1')
                ->example('test 2'),

            ImportColumn::make('product_option_2_is_custom')
                ->rules(['nullable', 'string', 'max:100'])
                ->exampleHeader('Product option 2 is custom')
                ->example('test 2'),

            ImportColumn::make('product_option_2_value_1_icon_type')
                ->rules(['nullable', 'string', 'max:100'])
                ->exampleHeader('Product option 2 value 1 icon type')
                ->example('test 2'),

            ImportColumn::make('product_option_2_value_1_icon_value')
                ->rules(['nullable', 'string', 'max:100'])
                ->exampleHeader('Product option 2 value 1 icon value')
                ->example('test 2'),
        ];
    }

    #[\Override]
    public function resolveRecord(): ProductVariant
    {
        return ProductVariant::firstOrNew([
            'sku' => $this->data['sku'],
        ]);
    }

    /**
     * @throws \Throwable
     */
    #[\Override]
    public function saveRecord(): void
    {
        if ($this->record->exists) {
            return;
        }

        DB::transaction(function () {

            $combination = $this->combinations($this->data, $this->record->product);

            ProductVariant::create(
                [
                    'product_id' => $this->record->product_id,
                    'sku' => $this->record->sku,
                    'combination' => $combination,
                    'retail_price' => $this->record->retail_price,
                    'selling_price' => $this->record->selling_price,
                    'stock' => $this->record->stock,
                    'status' => true,
                ]
            );
        });

    }

    private function combinations(array $row, Product $product): array
    {
        $combination = [];
        for ($i = 1; $i <= 2; $i++) {
            $iconType = 'text';
            $iconValue = '';

            if (! isset($row["product_option_{$i}_name"]) || ! $row["product_option_{$i}_name"]) {
                continue;
            }

            $foundProductOption = ProductOption::select('id', 'name')
                ->where('name', $row["product_option_{$i}_name"])
                ->where('product_id', $product->id)->first();

            if (isset($row["product_option_{$i}_value_1_icon_type"]) && $row["product_option_{$i}_value_1_icon_type"]) {
                $iconType = strtolower(str_replace(' ', '_', (string) $row["product_option_{$i}_value_1_icon_type"]));
            }

            if (isset($row["product_option_{$i}_value_1_icon_value"]) && $row["product_option_{$i}_value_1_icon_value"]) {
                $iconValue = $row["product_option_{$i}_value_1_icon_value"];
            }
            if ($foundProductOption instanceof ProductOption) {
                $foundProductOptionValue = ProductOptionValue::select('id', 'name')
                    ->where('product_option_id', $foundProductOption->id)
                    ->where('name', $row["product_option_{$i}_value_1"])
                    ->first();

                if (! $foundProductOptionValue instanceof ProductOptionValue) {
                    $productOptionValue = ProductOptionValue::create([
                        'name' => $row["product_option_{$i}_value_1"],
                        'product_option_id' => $foundProductOption->id,
                        'data' => [
                            'icon_type' => $i === 1 ? $iconType : 'text',
                            'icon_value' => $i == 1 ? $iconValue : '',

                        ],
                    ]);

                    $combination[$i - 1] = [
                        'option' => $foundProductOption->name,
                        'option_id' => $foundProductOption->id,
                        'option_value' => $productOptionValue->name,
                        'option_value_id' => $productOptionValue->id,
                    ];
                } else {
                    $combination[$i - 1] = [
                        'option' => $foundProductOption->name,
                        'option_id' => $foundProductOption->id,
                        'option_value' => $foundProductOptionValue->name,
                        'option_value_id' => $foundProductOptionValue->id,
                    ];
                }
            } else {
                $productOption = ProductOption::create([
                    'name' => $row["product_option_{$i}_name"],
                    'product_id' => $product->id,
                    'is_custom' => $i == 1 ? strtolower((string) $row["product_option_{$i}_is_custom"]) === 'yes' : false,
                ]);

                $productOptionValue = ProductOptionValue::create([
                    'name' => $row["product_option_{$i}_value_1"],
                    'product_option_id' => $productOption->id,
                    'data' => [
                        'icon_type' => $i === 1 ? $iconType : 'text',
                        'icon_value' => $i == 1 ? $iconValue : '',
                    ],
                ]);

                $combination[$i - 1] = [
                    'option' => $productOption->name,
                    'option_id' => $productOption->id,
                    'option_value' => $productOptionValue->name,
                    'option_value_id' => $productOptionValue->id,
                ];
            }
        }

        return $combination;
    }

    #[\Override]
    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your product variant import has completed and '.
            number_format($import->successful_rows).' '.
            Str::of('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.Str::of('row')
                ->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
