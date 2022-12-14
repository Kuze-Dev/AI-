<?php

namespace App\FilamentTenant\Resources\MenuResource\Pages;

use App\FilamentTenant\Resources\MenuResource;
use Domain\Menu\Actions\UpdateMenuAction;
use Domain\Menu\DataTransferObjects\MenuData;
use Domain\Menu\Models\Node;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditMenu extends EditRecord
{
    protected static string $resource = MenuResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function () {
                    $nodes = $this->record->nodes;

                    foreach ($nodes as $node) {
                        Node::find($node->id)->delete();
                    }
                }),
        ];
    }

    /** @param Menu $record */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(fn () => app(UpdateMenuAction::class)
            ->execute(
                $record,
                new MenuData(
                    name: $data['name'],
                    slug: $data['slug'],
                )
            ));
    }

    protected function getRedirectUrl(): ?string
    {
        return MenuResource::getUrl('edit', $this->record);
    }
}
