<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ProductResource\Pages;

use App\FilamentTenant\Resources\ProductResource;
use App\FilamentTenant\Support\ImportProductAction;
use Domain\Product\Enums\Decision;
use Domain\Product\Enums\Status;
use Domain\Product\Models\Product;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Support\Excel\Actions\ExportAction;
use Illuminate\Database\Eloquent\Builder;
use Support\Excel\Actions\ImportAction;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getActions(): array
    {
        return [
            ImportProductAction::proceed(),
            ImportAction::make('Upload for Batch Update'),
            ExportAction::make()
                ->model(Product::class)
                ->queue()
                ->query(fn (Builder $query) => $query->with('productVariants')->latest())
                ->mapUsing(
                    [
                        'Product ID', 'Is Variant', 'Variant ID', 'Name', 'Variant Combination', 'SKU',
                        'Retail Price', 'Selling Price', 'Stock', 'Status', 'Is Digital Product',
                        'Is Featured', 'Is Special Offer', 'Allow Customer Remarks', 'Allow Stocks',
                        'Allow Guest Purchase', 'Weight', 'Dimension', 'Minimum Order Quantity',
                    ],
                    function (Product $product) {
                        $a = [
                            [
                                $product->id,
                                'no',
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
                                $product->dimension,
                                $product->minimum_order_quantity,
                            ],
                        ];
                        foreach ($product->productVariants as $variant) {
                            $a[] =
                                [
                                    $variant->product_id,
                                    'yes',
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
}
