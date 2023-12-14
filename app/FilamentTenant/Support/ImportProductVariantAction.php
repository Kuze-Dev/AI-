<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support;

use Domain\Product\Models\Product;
use Domain\Product\Models\ProductOption;
use Domain\Product\Models\ProductOptionValue;
use Domain\Product\Models\ProductVariant;
use HalcyonAgile\FilamentImport\Actions\ImportAction;
use Illuminate\Validation\ValidationException;
use Support\Common\Rules\MinimumValueRule;

class ImportProductVariantAction
{
    public static function proceed(): ImportAction
    {
        return ImportAction::make('Product Variant Import')
            ->translateLabel()
            ->uniqueBy('sku')
            ->processRowsUsing(fn (array $row): ProductVariant => self::processProductVariantUpload($row))
            ->withValidation(
                rules: [
                    'product_slug' => 'required|string|max:100',
                    'sku' => 'required|string|unique:product_variants|max:100',
                    'stock' => ['required', 'numeric', new MinimumValueRule(0)],
                    'retail_price' => ['required', 'numeric', new MinimumValueRule(0.1)],
                    'selling_price' => ['required', 'numeric', new MinimumValueRule(0.1)],
                ],
            );
    }

    public static function processProductVariantUpload(array $row): ProductVariant
    {
        $product = Product::select('id')->whereSlug($row['product_slug'])->first();

        if (! $product instanceof Product) {
            throw ValidationException::withMessages([
                'product_slug' => trans("{$row['sku']}'s product slug doesn\'t have any matches in database."),
            ]);
        }

        $combination = [];
        for ($i = 1; $i <= 2; $i++) {
            $foundProductOption = ProductOption::select('id', 'name')
                ->where('name', $row["product_option_{$i}_name"])
                ->where('product_id', $product->id)->first();

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
                            'icon_type' => $i === 1 ? (strtolower(str_replace(' ', '_', $row["product_option_{$i}_value_1_icon_type"]))) : 'text',
                            'icon_value' => $i == 1 ? $row["product_option_{$i}_value_1_icon_value"] : '',

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
                    'is_custom' => $i == 1 ? strtolower($row["product_option_{$i}_is_custom"]) === 'yes' : false,
                ]);

                $productOptionValue = ProductOptionValue::create([
                    'name' => $row["product_option_{$i}_value_1"],
                    'product_option_id' => $productOption->id,
                    'data' => [
                        'icon_type' => $i === 1 ? (strtolower(str_replace(' ', '_', $row["product_option_{$i}_value_1_icon_type"]))) : 'text',
                        'icon_value' => $i == 1 ? $row["product_option_{$i}_value_1_icon_value"] : '',
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

        \Log::info('Import Product Variant SKU : ', [$row['sku']]);

        return ProductVariant::create([
            'product_id' => $product->id,
            'sku' => $row['sku'],
            'combination' => $combination,
            'retail_price' => $row['retail_price'],
            'selling_price' => $row['selling_price'],
            'stock' => $row['stock'],
            'status' => true,
        ]);
    }
}
