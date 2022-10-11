<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Domain\Admin\Actions\CreateRoleAction;
use Domain\Admin\DataTransferObjects\RoleData;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(CreateRoleAction::class)->execute(new RoleData(...$data));
    }
}
