<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ProductResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\ProductResource;
use App\FilamentTenant\Support\Concerns\HasProductOptions;
use App\FilamentTenant\Support\Concerns\HasProductVariants;
use App\FilamentTenant\Support\Contracts\HasProductOptions as HasProductOptionsContracts;
use App\FilamentTenant\Support\Contracts\HasProductVariants as HasProductVariantsContracts;
use App\FilamentTenant\Support\ProductOptionFormAction;
use App\FilamentTenant\Support\ProductVariantFormAction;
use Domain\Product\Actions\CreateProductAction;
use Domain\Product\DataTransferObjects\ProductData;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateProduct extends CreateRecord implements HasProductOptionsContracts, HasProductVariantsContracts
{
    use HasProductOptions;
    use HasProductVariants;
    use LogsFormActivity;

    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label(trans('filament::resources/pages/create-record.form.actions.create.label'))
                ->action('create')
                ->keyBindings(['mod+s']),
            ProductOptionFormAction::make(),
            ProductVariantFormAction::make(),
            $this->getCreateAnotherFormAction(),
        ];
    }

    /** @throws Throwable */
    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(fn () => app(CreateProductAction::class)->execute(ProductData::fromArray($data)));
    }
}
