<?php

declare(strict_types=1);

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Domain\Role\Actions\UpdateRoleAction;
use Domain\Role\DataTransferObjects\RoleData;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * @param  \Domain\Role\Models\Role  $record
     * @throws Throwable
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(fn () => app(UpdateRoleAction::class)->execute($record, new RoleData(...$data)));
    }
}
