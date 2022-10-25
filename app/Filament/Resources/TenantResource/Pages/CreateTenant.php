<?php

declare(strict_types=1);

namespace App\Filament\Resources\TenantResource\Pages;

use App\Filament\Resources\TenantResource;
use Domain\Tenant\Actions\CreateTenantAction;
use Domain\Tenant\DataTransferObjects\TenantData;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;

    public function handleRecordCreation(array $data): Model
    {
        return app(CreateTenantAction::class)->execute(new TenantData(...$data));
    }
}
