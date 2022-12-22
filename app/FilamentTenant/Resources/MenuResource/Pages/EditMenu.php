<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\MenuResource\Pages;

use App\FilamentTenant\Resources\MenuResource;
use Domain\Menu\Actions\UpdateMenuAction;
use Domain\Menu\DataTransferObjects\MenuData;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Domain\Menu\Models\Menu;

class EditMenu extends EditRecord
{
    protected static string $resource = MenuResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * @param \Domain\Menu\Models\Menu $record
     * @throws Throwable
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(fn () => app(UpdateMenuAction::class)->execute($record, MenuData::fromArray(($data))));
    }

    protected function getRedirectUrl(): ?string
    {
        return MenuResource::getUrl('edit', $this->record);
    }
}
