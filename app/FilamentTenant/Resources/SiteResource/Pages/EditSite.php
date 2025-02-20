<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\SiteResource\Pages;

use App\FilamentTenant\Resources\SiteResource;
use Domain\Site\Actions\UpdateSiteAction;
use Domain\Site\DataTransferObjects\SiteData;
use Domain\Site\Models\Site;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditSite extends EditRecord
{
    protected static string $resource = SiteResource::class;

    /**
     * Declare action buttons that
     * are available on the page.
     */
    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label(trans('filament::resources/pages/edit-record.form.actions.save.label'))
                ->action('save')
                ->keyBindings(['mod+s']),
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * @param  Site  $record
     */
    #[\Override]
    protected function handleRecordUpdate(Model $record, array $data): Model
    {

        return app(UpdateSiteAction::class)
            ->execute($record, SiteData::fromArray($data));
    }
}
