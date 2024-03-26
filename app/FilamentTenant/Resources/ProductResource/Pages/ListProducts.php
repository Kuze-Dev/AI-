<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ProductResource\Pages;

use App\Features\ECommerce\ProductBatchUpdate;
use App\FilamentTenant\Resources\ProductResource;
use Domain\Product\Enums\Decision;
use Domain\Product\Enums\Status;
use Domain\Product\Exports\ProductExporter;
use Domain\Product\Imports\ProductBatchUpdateImporter;
use Domain\Product\Imports\ProductImporter;
use Domain\Product\Imports\ProductVariantImporter;
use Domain\Product\Models\Product;
use Domain\Tenant\TenantFeatureSupport;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use HalcyonAgile\FilamentExport\Actions\ExportAction;
use Illuminate\Database\Eloquent\Builder;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //            Actions\ImportAction::make()
            //                ->importer(ProductImporter::class)
            //                ->icon('heroicon-o-arrow-up-tray')
            //                ->color('primary'),

            Actions\ImportAction::make()
                ->importer(ProductVariantImporter::class)
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary'),

            Actions\ImportAction::make('import_batch_update')
                ->label(trans('Import batch update'))
                ->modalHeading(trans('Import batch update'))
                ->importer(ProductBatchUpdateImporter::class)
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->visible(fn () => TenantFeatureSupport::active(ProductBatchUpdate::class)),

            Actions\ExportAction::make('export')
                ->exporter(ProductExporter::class)
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary'),

            Actions\CreateAction::make(),
        ];

        return [
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
