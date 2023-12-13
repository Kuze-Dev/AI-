<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support;

use App\Features\ECommerce\ProductBatchUpdate;
use Domain\Product\Actions\UpdateProductAction;
use Domain\Product\Actions\UpdateProductVariantFromCsvAction;
use Domain\Product\DataTransferObjects\ProductData;
use Domain\Product\DataTransferObjects\ProductVariantData;
use Domain\Product\Enums\Decision;
use Domain\Product\Enums\Status;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductVariant;
use HalcyonAgile\FilamentImport\Actions\ImportAction;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;
use Support\Common\Rules\MinimumValueRule;

class ImportProductBatchUpdateAction
{
    public static function proceed(): ImportAction
    {
        return ImportAction::make('Batch Update Import')
            ->uniqueBy('sku')
            ->hidden(fn () => ! tenancy()->tenant?->features()->active(ProductBatchUpdate::class) ? true : false)
            ->translateLabel()
            ->processRowsUsing(fn (array $row) => self::processBatchUpdate($row))
            ->withValidation(
                rules: [
                    'product_id' => 'required|numeric',
                    'is_variant' => ['required', new Enum(Decision::class)],
                    'variant_id' => 'nullable|integer',
                    'name' => 'nullable|string|max:100',
                    'variant_combination' => 'string|nullable',
                    'sku' => 'required|string|max:100',
                    'retail_price' => ['required', 'numeric', new MinimumValueRule(0.1)],
                    'selling_price' => ['required', 'numeric', new MinimumValueRule(0.1)],
                    'stock' => ['required', 'numeric', new MinimumValueRule(0)],
                    'status' => ['required', new Enum(Status::class)],
                    'is_digital_product' => ['nullable', new Enum(Decision::class)],
                    'is_featured' => ['nullable', new Enum(Decision::class)],
                    'is_special_offer' => ['nullable', new Enum(Decision::class)],
                    'allow_customer_remarks' => ['nullable', new Enum(Decision::class)],
                    'allow_stocks' => ['nullable', new Enum(Decision::class)],
                    'allow_guest_purchase' => ['nullable', new Enum(Decision::class)],
                    'weight' => ['nullable', 'numeric', new MinimumValueRule(0.1)],
                    'length' => ['nullable', 'numeric', new MinimumValueRule(1)],
                    'width' => ['nullable', 'numeric', new MinimumValueRule(1)],
                    'height' => ['nullable', 'numeric', new MinimumValueRule(1)],
                ],
            );
    }

    public static function processBatchUpdate(array $row): Product|ProductVariant
    {
        // Product Variant Batch Update
        if ($row['is_variant'] == Decision::YES->value && $row['variant_id'] && $row['variant_combination']) {
            return self::processVariantBatchUpdate($row);
        }

        // Product Batch Update
        if ($row['is_variant'] == Decision::NO->value && $row['product_id'] && $row['name']) {
            return self::processProductBatchUpdate($row);
        }

        // Fail case in
        throw ValidationException::withMessages([
            'row_error' => trans("The data from row is insufficient. System can't process it."),
        ]);
    }

    protected static function processVariantBatchUpdate(array $row): ProductVariant
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

        return app(UpdateProductVariantFromCsvAction::class)
            ->execute($productVariant, ProductVariantData::fromArray($variantData));
    }

    protected static function processProductBatchUpdate(array $row): Product
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

        return app(UpdateProductAction::class)->execute($foundProduct, ProductData::fromCsvBulkUpdate($data));
    }
}
