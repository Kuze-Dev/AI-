<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\SiteResource\Pages;

use App\FilamentTenant\Resources\SiteResource;
use Domain\Site\Actions\CreateSiteAction;
use Domain\Site\DataTransferObjects\SiteData;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateSite extends CreateRecord
{
    protected static string $resource = SiteResource::class;

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

        return DB::transaction(
            fn () => app(CreateSiteAction::class)
                ->execute(SiteData::fromArray($data))
        );
    }

    protected function getFormActions(): array
    {
        return $this->getCachedActions();
    }
}
