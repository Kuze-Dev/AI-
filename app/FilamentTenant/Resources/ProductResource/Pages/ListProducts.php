<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ProductResource\Pages;

use App\FilamentTenant\Resources\ProductResource;
use App\FilamentTenant\Support\ImportProductAction;
use Domain\Product\Actions\CreateOrUpdateProductVariantAction;
use Domain\Product\Actions\UpdateProductAction;
use Domain\Product\Actions\UpdateProductVariantFromCsvAction;
use Domain\Product\DataTransferObjects\ProductData;
use Domain\Product\DataTransferObjects\ProductVariantData;
use Domain\Product\Enums\Decision;
use Domain\Product\Enums\Status;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductVariant;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Support\Excel\Actions\ExportAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Enum;
use Support\Common\Rules\MinimumValueRule;
use Support\Excel\Actions\ImportAction;
use Illuminate\Validation\ValidationException;
use Log;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getActions(): array
    {
        return [
            ImportProductAction::proceed(),
            ImportAction::make('Batch Update Import')
                ->translateLabel()
                ->processRowsUsing(fn (array $row) => self::processBatchUpdate($row))
                ->withValidation(
                    rules: [
                        'product_id' => 'required|integer|max:100',
                        'is_variant' => ['required', new Enum(Decision::class)],
                        'variant_id' => 'nullable|integer|max:100',
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
                ),
            ExportAction::make()
                ->model(Product::class)
                ->queue()
                ->query(fn (Builder $query) => $query->with('productVariants')->latest())
                ->mapUsing(
                    [
                        'product_id', 'is_variant', 'variant_id', 'name', 'variant_combination', 'sku',
                        'retail_price', 'selling_price', 'stock', 'status', 'is_digital_product',
                        'is_featured', 'is_special_offer', 'allow_customer_remarks', 'allow_stocks',
                        'allow_guest_purchase', 'weight', 'length', 'width', 'height', 'minimum_order_quantity',
                    ],
                    function (Product $product) {
                        $a = [
                            [
                                $product->id,
                                Decision::NO->value,
                                '',
                                $product->name,
                                '',
                                $product->sku,
                                $product->retail_price,
                                $product->selling_price,
                                $product->stock,
                                $product->status ? Status::ACTIVE->value : STATUS::INACTIVE->value,
                                $product->is_digital_product ? Decision::YES->value : Decision::NO->value,
                                $product->is_featured ? Decision::YES->value : Decision::NO->value,
                                $product->is_special_offer ? Decision::YES->value : Decision::NO->value,
                                $product->allow_customer_remarks ? Decision::YES->value : Decision::NO->value,
                                $product->allow_stocks ? Decision::YES->value : Decision::NO->value,
                                $product->allow_guest_purchase ? Decision::YES->value : Decision::NO->value,
                                $product->weight,
                                $product->dimension['length'],
                                $product->dimension['width'],
                                $product->dimension['height'],
                                $product->minimum_order_quantity,
                            ],
                        ];
                        foreach ($product->productVariants as $variant) {
                            $a[] =
                                [
                                    $variant->product_id,
                                    Decision::YES->value,
                                    $variant->id,
                                    '',
                                    $variant->combination,
                                    $variant->sku,
                                    $variant->retail_price,
                                    $variant->selling_price,
                                    $variant->stock,
                                    $variant->status ? Status::ACTIVE->value : STATUS::INACTIVE->value,

                                ];
                        }

                        return $a;
                    }
                ),
            Actions\CreateAction::make(),
        ];
    }

    public static function processBatchUpdate(array $row)
    {
        // Check if for variant
        if ($row['is_variant'] == Decision::YES->value && $row['variant_id'] && $row['variant_combination']) {
            $decodedPayloadVariantCombination = json_decode($row['variant_combination'], true);

            $productVariant = ProductVariant::find($row['variant_id']);

            if (!$productVariant) {
                throw ValidationException::withMessages([
                    'variant_id' => trans("There is no matching variant record for the Variant ID."),
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

            if (!$product) {
                throw ValidationException::withMessages([
                    'product_id' => trans("There is no matching product record for the Product ID."),
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
        
        if ($row['is_variant'] == Decision::NO->value && $row['product_id'] && $row['name']) {
            $foundProduct = Product::whereId($row['product_id'])->with(['productOptions.productOptionValues', 'productVariants', 'media'])->first();

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
            ];

            // Set the meta data for the pro1duct
            $data['meta_data'] = ['title' => $data['name']];
            if ($foundProduct) {
                $data['product_options'] = $foundProduct->productOptions->map(function ($option) {
                    return [
                        'id' => $option->id,
                        'name' => $option->name,
                        'slug' => $option->slug,
                        'productOptionValues' => $option->productOptionValues->toArray(),
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

        throw ValidationException::withMessages([
            'row_error' => trans("The data from row is insufficient. System can't process it."),
        ]);
    }
}
