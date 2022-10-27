<?php

declare(strict_types=1);

namespace App\Filament\Resources\TenantResource\Pages;

use App\Filament\Resources\TenantResource;
use Domain\Tenant\Actions\UpdateTenantAction;
use Domain\Tenant\DataTransferObjects\TenantData;
use Domain\Tenant\Models\Tenant;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditTenant extends EditRecord
{
    protected static string $resource = TenantResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /** @param Tenant $record */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(UpdateTenantAction::class)->execute($record, new TenantData(...$data));
    }
}
