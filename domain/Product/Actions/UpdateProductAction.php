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

        $toRemoveOptions = [];


        if (count($productData->product_options)) {
            // dd($productData->product_options);
            foreach ($productData->product_options[0] as $productOption) {
                $productOptionModel = ProductOption::findOrNew($productOption['id']);
                $productOptionModel->name = $productOption['name'];
                $productOptionModel->save();

                foreach ($productOption['productOptionValues'] as $key => $productOptionValue) {
                    $optionValueModel = ProductOptionValue::findOrNew($productOptionValue['id']);
                    $optionValueModel->name = $productOptionValue['name'];
                    $optionValueModel->product_option_id = $productOptionValue['product_option_id'];
                    $optionValueModel->save();

                    $productOption['productOptionValues'][$key]['id'] = $optionValueModel->id;
                }

                // Remove option values
                $uwu = array_map(function ($item) {
                    return $item['id'];
                }, $productOption['productOptionValues']);
                // dd($uwu);
                $toRemoveOptionValues = ProductOptionValue::where('product_option_id', $productOptionModel->id)->whereNotIn('id', $uwu)->get();
                // dd('to remove shh : ', $toRemoveOptionValues);

                if (count($toRemoveOptionValues->toArray())) {
                    array_push($toRemoveOptions, array_map(function ($item) use ($productOptionModel) {
                        return [
                            'option_id' => $productOptionModel->id,
                            'option_value_id' => $item['id'],
                        ];
                    }, $toRemoveOptionValues->toArray()));
                }
                foreach ($toRemoveOptionValues as $optionValue) {
                    // $optionValue->delete();
                }
            }

            // to delete product variant value
            // dd($toRemoveOptions);
            // dd($toRemoveOptions);
            foreach ($toRemoveOptions as $toRemoveOption) {
                foreach ($toRemoveOption as $toRemove) {

                    $toRemoveProductVariant = ProductVariant::where('product_id', $product->id)
                        // ->whereHas('combination', function ($query) use ($toRemove){
                        //     $query->where('option_id', $toRemove['option_id'])
                        //     ->where('option_value_id', $toRemove['option_value_id']);
                        // })  
                        // ->whereHas('combination', $toRemove['option_id'])
                        // ->whereJsonContains('combination', $toRemove)
                        ->get();



                    dd($toRemoveProductVariant);
                    foreach ($toRemoveProductVariant as $prodVariant) {
                        $variantCombinations = $prodVariant->combination;
                        dd($variantCombinations, $toRemove);
                        foreach($variantCombinations as $key => $combination) {
                            if ($combination['option_id'] === $toRemove['option_id'] && $combination['option_value_id'] === $toRemove['option_value_id']) {
                                
                                    \Log::info('naditooooooooooo ', $key );
                                // $prodVariant->delete();
                            }
                        }
                    }
                }
            }
        }

        foreach ($productData->product_variants as $productVariant) {
            $productVariantModel = ProductVariant::findOrNew($productVariant['id']);

            $productVariantModel->product_id = $product['id'];
            $productVariantModel->sku = $productVariant['sku'];
            $productVariantModel->combination = $productVariant['combination'];
            $productVariantModel->retail_price = $productVariant['retail_price'];
            $productVariantModel->selling_price = $productVariant['selling_price'];
            $productVariantModel->stock = $productVariant['stock'];
            $productVariantModel->status = $productVariant['status'];
            $productVariantModel->save();
        }



        if ($productData->images === null) {
            $product->clearMediaCollection('image');
        }

        $product->taxonomyTerms()->sync($productData->taxonomy_terms);

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
