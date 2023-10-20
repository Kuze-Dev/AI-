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
use Illuminate\Database\Eloquent\Builder;
use Support\Excel\Actions\ExportAction;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getActions(): array
    {
        return [
            ImportProductAction::proceed(),
            ExportAction::make()
                ->model(Product::class)
                ->queue()
                ->query(fn (Builder $query) => $query->latest())
                ->mapUsing(
                    [
                        'Name', 'SKU', 'Description', 'Retail Price', 'Selling Price', 'Stock',
                        'Status', 'Is Digital Product', 'Is Featured', 'Is Special Offer',
                        'Allow Customer Remarks', 'Weight', 'Dimension', 'Minimum Order Quantity',
                        'Created At',
                    ],
                    fn (Product $product): array => [
                        $product->name,
                        $product->sku,
                        $product->description,
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
                        $product->created_at?->format(config('tables.date_time_format')),
                    ]
                ),
            Actions\CreateAction::make(),
        ];
    }
}
