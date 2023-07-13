<?php

declare(strict_types=1);

namespace Domain\Product\Actions;

use Domain\Product\DataTransferObjects\ProductData;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductOption;
use Domain\Product\Models\ProductOptionValue;
use Domain\Product\Models\ProductVariant;
use Support\MetaData\Actions\CreateMetaDataAction;
use Support\MetaData\Actions\UpdateMetaDataAction;
use Illuminate\Http\UploadedFile;

class UpdateProductAction
{
    public function __construct(
        protected CreateMetaDataAction $createMetaData,
        protected UpdateMetaDataAction $updateMetaData,
    ) {
    }

    public function execute(Product $product, ProductData $productData): Product
    {
        $product->update($this->getProductAttributes($productData));

        $product->metaData()->exists()
            ? $this->updateMetaData->execute($product, $productData->meta_data)
            : $this->createMetaData->execute($product, $productData->meta_data);

        foreach ($productData->images as $image) {
            if ($image instanceof UploadedFile && $imageString = $image->get()) {
                $product->addMediaFromString($imageString)
                    ->usingFileName($image->getClientOriginalName())
                    ->usingName(pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME))
                    ->toMediaCollection('image');
            }
        }

        if (count($productData->product_options)) {
            foreach ($productData->product_options[0] as $key => &$productOption) {
                $productOptionModel = ProductOption::find($productOption['id']);

                if ($productOptionModel) {
                    $productOptionModel->product_id = $product->id;
                    $productOptionModel->name = $productOption['name'];
                    $productOptionModel->save();
                } else {
                    $newProductOptionModel = ProductOption::create([
                        'product_id' => $product->id,
                        'name' => $productOption['name'],
                    ]);

                    $productData->product_variants = $this->searchAndChangeValue(
                        $productOption['id'],
                        $productData->product_variants,
                        $newProductOptionModel->id
                    );
                    $productData->product_options[0][$key]['id'] = $newProductOptionModel->id;
                }

                foreach ($productOption['productOptionValues'] as $key2 => $productOptionValue) {
                    $optionValueModel = ProductOptionValue::find($productOptionValue['id']);

                    if ($optionValueModel) {
                        $optionValueModel->name = $productOptionValue['name'];
                        $optionValueModel->product_option_id = $productOption['id'];
                        $optionValueModel->save();
                    } else {
                        $newOptionValueModel = ProductOptionValue::create([
                            'name' => $productOptionValue['name'],
                            'product_option_id' => $productOption['id'],
                        ]);

                        $productOption['productOptionValues'][$key2]['id'] = $newOptionValueModel->id;
                        $productData->product_variants = $this->searchAndChangeValue(
                            $productOptionValue['id'],
                            $productData->product_variants,
                            $newOptionValueModel->id,
                            'option_value_id'
                        );
                    }
                }

                // Removal of Option Values
                $mappedOptionValueIds = array_map(function ($item) {
                    return $item['id'];
                }, $productOption['productOptionValues']);
                if (count($mappedOptionValueIds)) {
                    $toRemoveOptionValues = ProductOptionValue::where(
                        'product_option_id',
                        $productOption['id']
                    )->whereNotIn('id', $mappedOptionValueIds)->get();
                    foreach ($toRemoveOptionValues as $optionValue) {
                        $optionValue->delete();
                    }
                }
            }

            // Removal of product options
            $mappedOptionIds = array_map(function ($item) {
                return $item['id'];
            }, $productData->product_options[0]);

            if (count($mappedOptionIds)) {
                $toRemoveOptions = ProductOption::where(
                    'product_id',
                    $product->id
                )->whereNotIn('id', $mappedOptionIds)->get();
                foreach ($toRemoveOptions as $option) {
                    $option->delete();
                }
            }
        }

        if (count($productData->product_variants)) {
            // Remove of Product Variants
            $mappedVariantIds = array_map(function ($item) {
                return $item['id'];
            }, $productData->product_variants);
            if (count($mappedVariantIds)) {
                $toRemoveProductVariants = ProductVariant::where('product_id', $product->id)
                    ->whereNotIn('id', $mappedVariantIds)->get();

                foreach ($toRemoveProductVariants as $productVariant) {
                    $productVariant->delete();
                }
            }

            foreach ($productData->product_variants as $productVariant) {
                $productVariantModel = ProductVariant::find($productVariant['id']);
                if ($productVariantModel) {
                    $productVariantModel->product_id = $product['id'];
                    $productVariantModel->sku = $productVariant['sku'];
                    $productVariantModel->combination = $productVariant['combination'];
                    $productVariantModel->retail_price = $productVariant['retail_price'];
                    $productVariantModel->selling_price = $productVariant['selling_price'];
                    $productVariantModel->stock = $productVariant['stock'];
                    $productVariantModel->status = $productVariant['status'];
                    $productVariantModel->save();
                } else {
                    ProductVariant::create([
                        'product_id' => $product['id'],
                        'sku' => $productVariant['sku'],
                        'combination' => $productVariant['combination'],
                        'retail_price' => $productVariant['retail_price'],
                        'selling_price' => $productVariant['selling_price'],
                        'stock' => $productVariant['stock'],
                        'status' => $productVariant['status'],
                    ]);
                }
            }
        }

        if ($productData->images === null) {
            $product->clearMediaCollection('image');
        }

        $product->taxonomyTerms()->sync($productData->taxonomy_terms);

        return $product;
    }

    private function searchAndChangeValue($needle, &$haystack, $newValue, $field = 'option_id')
    {
        foreach ($haystack as $key => $variant) {
            foreach ($variant['combination'] as $key2 => $combination) {
                if ($combination[$field] == $needle) {
                    $haystack[$key]['combination'][$key2][$field] = $newValue;
                }
            }
        }

        return $haystack;
    }

    protected function getProductAttributes(ProductData $productData): array
    {
        return array_filter(
            [
                'name' => $productData->name,
                'sku' => $productData->sku,
                'description' => $productData->description,
                'retail_price' => $productData->retail_price,
                'selling_price' => $productData->selling_price,
                'weight' => $productData->weight,
                'status' => $productData->status,
                'stock' => $productData->stock,
                'minimum_order_quantity' => $productData->minimum_order_quantity,
                'is_digital_product' => $productData->is_digital_product,
                'is_featured' => $productData->is_featured,
                'is_special_offer' => $productData->is_special_offer,
                'allow_customer_remarks' => $productData->allow_customer_remarks,
                'dimension' => ['length' => $productData->length, 'width' => $productData->width, 'height' => $productData->height],
            ],
            fn ($value) => filled($value)
        );
    }
}
