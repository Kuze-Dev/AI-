<?php

declare(strict_types=1);

namespace App\Filament\Resources\TenantResource\Pages;

use App\Filament\Resources\TenantResource;
use Domain\Tenant\Actions\CreateTenantAction;
use Domain\Tenant\DataTransferObjects\TenantData;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;

    protected function getRules(): array
    {
        return tap(
            parent::getRules(),
            fn (&$rules) => $rules['data.domains.*.domain'] = ['distinct']
        );
    }

    protected function afterValidate(): void
    {
        $this->validate();
    }

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
    public function handleRecordCreation(array $data): Model
    {
        return DB::transaction(fn () => app(CreateTenantAction::class)->execute(TenantData::fromArray($data)));
    }
}
