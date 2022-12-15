<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\MenuResource\Pages;

use App\FilamentTenant\Resources\MenuResource;
use Domain\Menu\Actions\CreateMenuAction;
use Domain\Menu\DataTransferObjects\MenuData;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateMenu extends CreateRecord
{
    protected static string $resource = MenuResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(fn () => app(CreateMenuAction::class)
            ->execute(new MenuData(
                name: $data['name'],
                slug: $data['slug']
            )));
    }
}
