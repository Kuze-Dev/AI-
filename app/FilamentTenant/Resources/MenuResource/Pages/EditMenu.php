<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\MenuResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\MenuResource;
use App\FilamentTenant\Support\Concerns\HasTrees;
use App\FilamentTenant\Support\Contracts\HasTrees as HasTreesContract;
use App\FilamentTenant\Support\TreeFormAction;
use Domain\Menu\Actions\UpdateMenuAction;
use Domain\Menu\DataTransferObjects\MenuData;
use Filament\Pages\Actions;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditMenu extends EditRecord implements HasTreesContract
{
    use HasTrees;
    use LogsFormActivity;

    protected static string $resource = MenuResource::class;

    protected function getActions(): array
    {
        return [
            Action::make('save')
                ->label(__('filament::resources/pages/edit-record.form.actions.save.label'))
                ->action('save')
                ->keyBindings(['mod+s']),
            TreeFormAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
        return $this->getCachedActions();
    }

    /** @param \Domain\Menu\Models\Menu $record */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(fn () => app(UpdateMenuAction::class)->execute($record, MenuData::fromArray($data)));
    }

    protected function getRedirectUrl(): ?string
    {
        return MenuResource::getUrl('edit', $this->record);
    }
}
