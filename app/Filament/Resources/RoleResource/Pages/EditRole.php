<?php

declare(strict_types=1);

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\Filament\Resources\RoleResource;
use Domain\Role\Actions\UpdateRoleAction;
use Domain\Role\DataTransferObjects\RoleData;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

class EditRole extends EditRecord
{
    use LogsFormActivity;

    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label(trans('filament-panels::resources/pages/edit-record.form.actions.save.label'))
                ->action('save')
                ->keyBindings(['mod+s']),
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * @param  \Domain\Role\Models\Role  $record
     *
     * @throws Throwable
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(fn () => app(UpdateRoleAction::class)->execute($record, RoleData::fromArray($data)));
    }
}
