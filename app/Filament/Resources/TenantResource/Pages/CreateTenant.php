<?php

declare(strict_types=1);

namespace App\Filament\Resources\TenantResource\Pages;

use App\Filament\Resources\TenantResource;
use Domain\Tenant\Actions\CreateTenantAction;
use Domain\Tenant\DataTransferObjects\TenantData;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;

    /** @throws Throwable */
    public function handleRecordCreation(array $data): Model
    {
        return DB::transaction(fn () => app(CreateTenantAction::class)->execute(new TenantData(...$data)));
    }
}
