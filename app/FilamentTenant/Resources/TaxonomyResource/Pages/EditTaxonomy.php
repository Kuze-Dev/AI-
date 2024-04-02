<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\TaxonomyResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\TaxonomyResource;
use Domain\Taxonomy\Actions\UpdateTaxonomyAction;
use Domain\Taxonomy\DataTransferObjects\TaxonomyData;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditTaxonomy extends EditRecord
{
    use LogsFormActivity;

    protected static string $resource = TaxonomyResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label(trans('filament-panels::resources/pages/edit-record.form.actions.save.label'))
                ->action('save')
                ->keyBindings(['mod+s']),
            Actions\DeleteAction::make(),
        ];
    }

    /** @param  \Domain\Taxonomy\Models\Taxonomy  $record */
    #[\Override]
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(UpdateTaxonomyAction::class)->execute($record, TaxonomyData::fromArray($data));
    }

    #[\Override]
    protected function getRedirectUrl(): ?string
    {
        return TaxonomyResource::getUrl('edit', [$this->record]);
    }
}
