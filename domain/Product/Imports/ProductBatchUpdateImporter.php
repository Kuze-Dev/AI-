<?php

declare(strict_types=1);

namespace Domain\Product\Imports;

use Domain\Product\Actions\UpdateProductAction;
use Domain\Product\Actions\UpdateProductVariantFromCsvAction;
use Domain\Product\DataTransferObjects\ProductData;
use Domain\Product\DataTransferObjects\ProductVariantData;
use Domain\Product\Enums\Decision;
use Domain\Product\Enums\Status;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductVariant;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;

class ProductBatchUpdateImporter extends Importer
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('is_variant')
                ->requiredMapping()
                ->rules(['required', Rule::in(['yes', 'no'])])
                ->exampleHeader('Is variant')
                ->example('yes'),

            ImportColumn::make('product_id')
                ->requiredMapping()
                ->rules(['required', Rule::exists(Product::class, 'id')])
                ->exampleHeader('Product id')
                ->example('1'),

            ImportColumn::make('variant_id')
                ->rules(['required_if:is_variant,yes', Rule::exists(ProductVariant::class, 'id')])
                ->exampleHeader('Variant id')
                ->example('1'),

            ImportColumn::make('name')
                ->rules(['nullable', 'string', 'max:100'])
                ->exampleHeader('Name')
                ->example('test'),

            ImportColumn::make('variant_combination')
                ->rules(['nullable', 'string', 'max:100'])
                ->exampleHeader('Variant combination')
                ->example('test'),

            ImportColumn::make('sku')
                ->rules(['nullable', 'string', 'max:100'])
                ->exampleHeader('SKU')
                ->example('test'),

            ImportColumn::make('retail_price')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'numeric', 'mn:0.1'])
                ->exampleHeader('Retail price')
                ->example('1.1'),

            ImportColumn::make('selling_price')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'numeric', 'mn:0.1'])
                ->exampleHeader('Selling price')
                ->example('1.1'),

            ImportColumn::make('stock')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'numeric', 'mn:0'])
                ->exampleHeader('Stock')
                ->example('1'),

            ImportColumn::make('status')
                ->requiredMapping()
                ->rules(['required', new Enum(Status::class)])
                ->exampleHeader('Status')
                ->example(Status::INACTIVE->value),
        ];
    }

    public function resolveRecord(): ?Model
    {
        // ignore
        return new Product();
    }

    public function saveRecord(): void
    {
        DB::beginTransaction();

        $row = $this->data;

        // Product Variant Batch Update
        if ($row['is_variant'] === Decision::YES->value && $row['variant_id'] && $row['variant_combination']) {
            self::processVariantBatchUpdate($row);
        }

        // Product Batch Update
        if ($row['is_variant'] === Decision::NO->value && $row['product_id'] && $row['name']) {
            self::processProductBatchUpdate($row);
        }

        // Fail case in
        throw ValidationException::withMessages([
            'row_error' => trans("The data from row is insufficient. System can't process it."),
        ]);
    }

    private static function processVariantBatchUpdate(array $row): void
    {
        $decodedPayloadVariantCombination = json_decode($row['variant_combination'], true);

        $productVariant = ProductVariant::find($row['variant_id']);

        if (! $productVariant instanceof ProductVariant) {
            throw ValidationException::withMessages([
                'variant_id' => trans('There is no matching variant record for the Variant ID.'),
            ]);
        }

        // If variant combination is matched
        if ($decodedPayloadVariantCombination != $productVariant->combination) {
            throw ValidationException::withMessages([
                'variant_combination' => trans("Row with Variant ID {$row['variant_id']} has mismatch combination."),
            ]);
        }

        if ($row['product_id'] != $productVariant->product_id) {
            throw ValidationException::withMessages([
                'product_id' => trans("Assigned product to row's variant is not matched with the row's product ID."),
            ]);
        }

        $product = Product::find($productVariant->product_id);

        if (! $product instanceof Product) {
            throw ValidationException::withMessages([
                'product_id' => trans('There is no matching product record for the Product ID.'),
            ]);
        }

        $variantData = [
            'id' => $row['variant_id'],
            'sku' => $row['sku'],
            'combination' => $decodedPayloadVariantCombination,
            'retail_price' => $row['retail_price'],
            'selling_price' => $row['selling_price'],
            'status' => $row['status'] == 'active' ? true : false,
            'stock' => $row['stock'],
            'product_id' => $row['product_id'],
        ];

        app(UpdateProductVariantFromCsvAction::class)
            ->execute($productVariant, ProductVariantData::fromArray($variantData));
    }

    private static function processProductBatchUpdate(array $row): void
    {
        $foundProduct = Product::whereId($row['product_id'])->with(['productOptions.productOptionValues.media', 'productVariants', 'media'])->first();

        if (! $foundProduct instanceof Product) {
            throw ValidationException::withMessages([
                'product_id' => trans('There is no matching product record for the Product ID.'),
            ]);
        }

        $data = [
            'name' => $row['name'],
            'sku' => $row['sku'],
            'retail_price' => $row['retail_price'],
            'selling_price' => $row['selling_price'],
            'length' => $row['length'],
            'width' => $row['width'],
            'height' => $row['height'],
            'weight' => $row['weight'],
            'stock' => $row['stock'],
            'meta_data' => ['title' => $row['name']],
        ];

        $data['product_options'] = $foundProduct->productOptions->map(function ($option) {
            return [
                'id' => $option->id,
                'name' => $option->name,
                'slug' => $option->slug,
                'is_custom' => $option->is_custom,
                'productOptionValues' => $option->productOptionValues->map(function ($optionValue) {
                    $optionValueToArray = $optionValue->toArray();

                    return [
                        'id' => $optionValueToArray['id'],
                        'name' => $optionValueToArray['name'],
                        'slug' => $optionValueToArray['slug'],
                        'product_option_id' => $optionValueToArray['product_option_id'],
                        'icon_type' => $optionValueToArray['data']['icon_type'] ?? 'text',
                        'icon_value' => $optionValueToArray['data']['icon_value'] ?? null,
                        'images' => array_map(fn ($image) => $image['uuid'], $optionValueToArray['media']),
                    ];
                })->toArray(),
            ];
        })->toArray();

        $data['product_variants'] = $foundProduct->productVariants->map(function ($variant) {
            return [
                'id' => $variant->id,
                'retail_price' => $variant->retail_price,
                'selling_price' => $variant->selling_price,
                'stock' => $variant->stock,
                'sku' => $variant->sku,
                'status' => $variant->status,
                'combination' => $variant->combination,
            ];
        })->toArray();

        app(UpdateProductAction::class)->execute($foundProduct, ProductData::fromCsvBulkUpdate($data));
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
