<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ProductResource\Pages;

use App\Features\ECommerce\ProductBatchUpdate;
use App\FilamentTenant\Resources\ProductResource;
use Domain\Product\Exports\ProductExporter;
use Domain\Product\Exports\ProductVariantExporter;
use Domain\Product\Imports\ProductBatchUpdateImporter;
use Domain\Product\Imports\ProductImporter;
use Domain\Product\Imports\ProductVariantImporter;
use Domain\Tenant\TenantFeatureSupport;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

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

            Actions\ExportAction::make('export_products')
                ->exporter(ProductExporter::class)
                ->columnMapping(false)
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary'),

            Actions\ExportAction::make('export_product_variants')
                ->exporter(ProductVariantExporter::class)
                ->columnMapping(false)
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary'),

            Actions\CreateAction::make(),
        ];
    }
}
