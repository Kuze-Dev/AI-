<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\PageResource\Pages;

use App\FilamentTenant\Resources\PageResource;
use Domain\Page\Actions\CreatePageAction;
use Domain\Page\DataTransferObjects\PageData;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreatePage extends CreateRecord
{
    protected static string $resource = PageResource::class;

    /** @throws Throwable */
    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(
            fn () => app(CreatePageAction::class)
                ->execute(new PageData(
                    name: $data['name'],
                    slug: $data['slug'],
                    blueprint_id: (int) $data['blueprint_id'],
                ))
        );
    }
}
