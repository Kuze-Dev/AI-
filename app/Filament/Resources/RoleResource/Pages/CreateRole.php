<?php

declare(strict_types=1);

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\Filament\Resources\RoleResource;
use Domain\Role\Actions\CreateRoleAction;
use Domain\Role\DataTransferObjects\RoleData;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateRole extends CreateRecord
{
    use LogsFormActivity;

    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label(trans('filament-panels::resources/pages/create-record.form.actions.create.label'))
                ->action('create')
                ->keyBindings(['mod+s']),
        ];
    }

    protected function handleRecordCreation(array $data): Model
    {
        return app(CreateRoleAction::class)->execute(RoleData::fromArray($data));
    }
}
