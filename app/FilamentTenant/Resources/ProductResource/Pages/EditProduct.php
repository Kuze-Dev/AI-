<?php

namespace App\FilamentTenant\Resources\ProductResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\ProductResource;
use Domain\Product\Actions\UpdateProductAction;
use Domain\Product\DataTransferObjects\ProductData;
use Filament\Pages\Actions;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditProduct extends EditRecord
{
    use LogsFormActivity;

    protected static string $resource = ProductResource::class;

    protected function getActions(): array
    {
        return [
            Action::make('save')
                ->label(__('filament::resources/pages/edit-record.form.actions.save.label'))
                ->action('save')
                ->keyBindings(['mod+s']),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
        return $this->getCachedActions();
    }

    /**
     * @param \Domain\Product\Models\Product $record
     * @throws Throwable
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(fn () => app(UpdateProductAction::class)->execute($record, ProductData::fromArray($data)));
    }

    protected function afterSave(): void
    {
        $this->record->refresh();
        $this->hasCachedForms = false;

        $this->fillForm();
    }
}
