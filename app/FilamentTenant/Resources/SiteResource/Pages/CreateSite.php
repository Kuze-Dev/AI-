<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\SiteResource\Pages;

use App\FilamentTenant\Resources\SiteResource;
use Domain\Site\Actions\CreateSiteAction;
use Domain\Site\DataTransferObjects\SiteData;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateSite extends CreateRecord
{
    protected static string $resource = SiteResource::class;

    /**
     * Execute database transaction
     * for creating collections.
     */
    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(
            fn () => app(CreateSiteAction::class)
                ->execute(new SiteData(
                    name: $data['name'],
                ))
        );
    }
}
