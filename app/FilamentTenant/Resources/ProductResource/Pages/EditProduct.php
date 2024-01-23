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
use Domain\Product\Actions\UpdateProductActionV2;
use Domain\Product\DataTransferObjects\ProductData;
use Filament\Pages\Actions;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

class EditProduct extends EditRecord implements HasProductOptionsContracts, HasProductVariantsContracts
{
    use HasProductOptions;
    use HasProductVariants;
    use LogsFormActivity;

    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label(trans('filament::resources/pages/edit-record.form.actions.save.label'))
                ->action('save')
                ->keyBindings(['mod+s']),
            ProductOptionFormAction::make(),
            ProductVariantFormAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
        return $this->getCachedActions();
    }

    /**
     * @param  \Domain\Product\Models\Product  $record
     *
     * @throws Throwable
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(fn () => app(UpdateProductActionV2::class)->execute($record, ProductData::fromArray($data)));
    }

    protected function afterSave(): void
    {
        $this->record->refresh();
        $this->hasCachedForms = false;

        $this->fillForm();
    }
}
