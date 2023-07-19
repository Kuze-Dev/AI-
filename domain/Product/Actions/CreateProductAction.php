<?php

declare(strict_types=1);

namespace Domain\Product\Actions;

use Domain\Product\DataTransferObjects\ProductData;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductOption;
use Domain\Product\Models\ProductOptionValue;
use Domain\Product\Models\ProductVariant;
use Support\MetaData\Actions\CreateMetaDataAction;
use Support\RouteUrl\Actions\CreateOrUpdateRouteUrlAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

class CreateProductAction
{
    public function __construct(
        protected CreateMetaDataAction $createMetaTags,
        protected CreateOrUpdateRouteUrlAction $createOrUpdateRouteUrl,
    ) {
    }

    public function execute(ProductData $productData): Product
    {
        /** @var Product $product */
        $product = Product::create($this->getProductAttributes($productData));

        $this->createMetaTags->execute($product, $productData->meta_data);

        if (count($productData->product_options)) {
            foreach ($productData->product_options as $key => &$productOption) {
                $newProductOptionModel = ProductOption::create([
                    'product_id' => $product->id,
                    'name' => $productOption['name'],
                ]);

                $productData->product_variants = $this->searchAndChangeValue(
                    $productOption['id'],
                    $productData->product_variants,
                    $newProductOptionModel->id
                );
                $productData->product_options[$key]['id'] = $newProductOptionModel->id;

                ////////
                foreach ($productOption['productOptionValues'] as $key2 => $productOptionValue) {
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
        }

        if (count($productData->product_variants)) {
            foreach ($productData->product_variants as $productVariant) {
                ProductVariant::create([
                    'product_id' => $product['id'],
                    'sku' => $productVariant['sku'],
                    'combination' => $productVariant['combination'],
                    'retail_price' => $productVariant['retail_price'],
                    'selling_price' => $productVariant['selling_price'],
                    'stock' => $productVariant['stock'],
                    'status' => $productVariant['status'] ?? 1,
                ]);
            }
        }

        /** clears unexpected media even before upload */
        $product->clearMediaCollection('image');
        if ($productData->images) {
            if (gettype($productData->images) === "string") {
                $response = Http::get($productData->images);
                if ($response->successful()) {
                    $product
                        ->addMediaFromUrl($productData->images)
                        ->toMediaCollection('image');
                }
            } else {
                foreach ($productData->images as $image) {
                    if ($image instanceof UploadedFile && $imageString = $image->get()) {
                        $product
                            ->addMediaFromString($imageString)
                            ->usingFileName($image->getClientOriginalName())
                            ->usingName(pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME))
                            ->toMediaCollection('image');
                    }
                }
            }
        }

        if ($productData->images === null) {
            $product->clearMediaCollection('image');
        }

        if ($productData->taxonomy_terms) {
            $product->taxonomyTerms()
                ->attach($productData->taxonomy_terms);
        }

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
