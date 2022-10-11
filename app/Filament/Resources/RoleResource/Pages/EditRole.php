<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Domain\Admin\Actions\UpdateRoleAction;
use Domain\Admin\DataTransferObjects\RoleData;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /** @param  Role  $record */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(UpdateRoleAction::class)->execute($record, new RoleData(...$data));
    }
}
