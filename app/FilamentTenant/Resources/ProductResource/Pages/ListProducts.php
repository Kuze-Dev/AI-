<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ProductResource\Pages;

use App\FilamentTenant\Resources\ProductResource;
use Domain\Product\Actions\CreateProductAction;
use Domain\Product\Actions\UpdateProductAction;
use Domain\Product\DataTransferObjects\ProductData;
use Domain\Product\Models\Product;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Support\Excel\Actions\ExportAction;
use Support\Excel\Actions\ImportAction;
use Illuminate\Database\Eloquent\Builder;
use Support\MetaData\DataTransferObjects\MetaDataData;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getActions(): array
    {
        return [
            ImportAction::make()
                ->model(Product::class)
                ->processRowsUsing(
                    function (array $row): Product {
                        $data = [
                            'name' => $row['name'],
                            'sku' => $row['sku'],
                            'description' => $row['description'],
                            'retail_price' => $row['retail_price'],
                            'selling_price' => $row['selling_price'],
                            'stock' => $row['stock'],
                            'status' => $row['status'],
                            'is_digital_product' => $row['is_digital_product'],
                            'is_featured' => $row['is_featured'],
                            'is_special_offer' => $row['is_special_offer'],
                            'allow_customer_remarks' => $row['allow_customer_remarks'],
                            'weight' => $row['weight'],
                            'length' => $row['length'],
                            'width' => $row['width'],
                            'height' => $row['height'],
                            'minimum_order_quantity' => $row['minimum_order_quantity'],
                        ];
                        unset($row);

                        if ($product = Product::whereName($data['name'])->first()) {
                            $product = app(UpdateProductAction::class)->execute($product, new ProductData(...$data));
                        } else {
                            $data['meta_data'] = new MetaDataData($data['name']);
                            $product = app(CreateProductAction::class)->execute(new ProductData(...$data));
                        }

                        return $product;
                    }
                )
                ->withValidation(
                    rules: [
                        'name' => 'required|string|min:3|max:100',
                    ],
                ),
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
                        $product->status ? 'Active' : 'Inactive',
                        $product->is_digital_product ? 'Yes' : 'No',
                        $product->is_featured ? 'Yes' : 'No',
                        $product->is_special_offer ? 'Yes' : 'No',
                        $product->allow_customer_remarks ? 'Yes' : 'No',
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
