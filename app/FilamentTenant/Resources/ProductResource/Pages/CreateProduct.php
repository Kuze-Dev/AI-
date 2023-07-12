<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ProductResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\ProductResource;
use Domain\Product\Actions\CreateProductAction;
use Domain\Product\DataTransferObjects\ProductData;
use Filament\Resources\Pages\CreateRecord;
use Filament\Pages\Actions\Action;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class CreateProduct extends CreateRecord
{
    use LogsFormActivity;

    protected static string $resource = ProductResource::class;

    protected function getActions(): array
    {
        return [
            Action::make('create')
                ->label(__('filament::resources/pages/create-record.form.actions.create.label'))
                ->action('create')
                ->keyBindings(['mod+s']),
            $this->getCreateAnotherFormAction(),
        ];
    }

    protected function getFormActions(): array
    {
        return $this->getCachedActions();
    }

    /** @throws Throwable */
    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(fn () => app(CreateProductAction::class)->execute(ProductData::fromArray($data)));
    }
}
