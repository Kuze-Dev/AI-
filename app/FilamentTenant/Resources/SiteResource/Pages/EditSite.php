<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\SiteResource\Pages;

use App\FilamentTenant\Resources\SiteResource;
use Domain\Site\Actions\UpdateSiteAction;
use Domain\Site\DataTransferObjects\SiteData;
use Domain\Site\Models\Site;
use Filament\Pages\Actions;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditSite extends EditRecord
{
    protected static string $resource = SiteResource::class;

    /**
     * Declare action buttons that
     * are available on the page.
     */
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
     * Execute database transaction
     * for updating collections.
     *
     * @param  Site  $record
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {

        return DB::transaction(
            fn () => app(UpdateSiteAction::class)
                ->execute($record, SiteData::fromArray($data))
        );
    }

    protected function getFormActions(): array
    {
        return $this->getCachedActions();
    }
}
