<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\SiteResource\Pages;

use App\FilamentTenant\Resources\SiteResource;
use Domain\Site\Actions\UpdateSiteAction;
use Domain\Site\DataTransferObjects\SiteData;
use Filament\Resources\Pages\EditRecord;
use Filament\Pages\Actions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Domain\Site\Models\Site;

class EditSite extends EditRecord
{
    protected static string $resource = SiteResource::class;

    /**
     * Declare action buttons that
     * are available on the page.
     */
    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * Execute database transaction
     * for updating collections.
     * @param Site $record
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(
            fn () => app(UpdateSiteAction::class)
                ->execute($record, new SiteData(
                    name: $data['name']
                ))
        );
    }
}
