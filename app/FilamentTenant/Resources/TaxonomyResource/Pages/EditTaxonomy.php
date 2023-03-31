<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\TaxonomyResource\Pages;

use App\FilamentTenant\Support\Concerns\HasTrees;
use App\FilamentTenant\Support\Contracts\HasTrees as HasTreesContract;
use App\FilamentTenant\Support\TreeFormAction;
use App\FilamentTenant\Resources\TaxonomyResource;
use Domain\Taxonomy\Actions\UpdateTaxonomyAction;
use Domain\Taxonomy\DataTransferObjects\TaxonomyData;
use Filament\Pages\Actions;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditTaxonomy extends EditRecord implements HasTreesContract
{
    use HasTrees;

    protected static string $resource = TaxonomyResource::class;

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

    /** @param \Domain\Taxonomy\Models\Taxonomy $record */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(fn () => app(UpdateTaxonomyAction::class)->execute($record, TaxonomyData::fromArray($data)));
    }

    protected function getRedirectUrl(): ?string
    {
        return TaxonomyResource::getUrl('edit', $this->record);
    }
}
