<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\MenuResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\MenuResource;
use Domain\Menu\Actions\CreateMenuAction;
use Domain\Menu\DataTransferObjects\MenuData;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateMenu extends CreateRecord
{
    use LogsFormActivity;

    protected static string $resource = MenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label(trans('filament::resources/pages/create-record.form.actions.create.label'))
                ->action('create')
                ->keyBindings(['mod+s']),
        ];
    }

    protected function handleRecordCreation(array $data): Model
    {
        return app(CreateMenuAction::class)->execute(MenuData::fromArray($data));
    }
}
