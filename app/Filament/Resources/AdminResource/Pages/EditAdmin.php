<?php

namespace App\Filament\Resources\AdminResource\Pages;

use App\Filament\Resources\AdminResource;
use Domain\Admin\Actions\UpdateAdminAction;
use Domain\Admin\DataTransferObjects\AdminData;
use Domain\Admin\Models\Admin;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditAdmin extends EditRecord
{
    protected static string $resource = AdminResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    /** @param  Admin  $record */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(UpdateAdminAction::class)->execute($record, new AdminData(...$data));
    }
}
