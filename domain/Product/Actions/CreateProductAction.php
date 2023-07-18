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
