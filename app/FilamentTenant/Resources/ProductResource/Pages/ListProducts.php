<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ProductResource\Pages;

use App\FilamentTenant\Resources\ProductResource;
use App\FilamentTenant\Support\ImportProductBatchUpdateAction;
use App\FilamentTenant\Support\ImportProductVariantAction;
use Domain\Product\Enums\Decision;
use Domain\Product\Enums\Status;
use Domain\Product\Models\Product;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use HalcyonAgile\FilamentExport\Actions\ExportAction;
use Illuminate\Database\Eloquent\Builder;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getActions(): array
    {
        return [
            // ImportProductAction::proceed(),
            ImportProductVariantAction::proceed(),
            ImportProductBatchUpdateAction::proceed(),
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
                        $productData = [
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
                                $product->dimension['length'] ?? '',
                                $product->dimension['width'] ?? '',
                                $product->dimension['height'] ?? '',
                                $product->minimum_order_quantity,
                            ],
                        ];
                        foreach ($product->productVariants as $variant) {
                            $productData[] =
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

                        return $productData;
                    }
                ),
            Actions\CreateAction::make(),
        ];
    }
}
