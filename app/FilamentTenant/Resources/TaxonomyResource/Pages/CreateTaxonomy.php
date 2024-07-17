<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\TaxonomyResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\TaxonomyResource;
use Domain\Taxonomy\Actions\CreateTaxonomyAction;
use Domain\Taxonomy\DataTransferObjects\TaxonomyData;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateTaxonomy extends CreateRecord
{
    use LogsFormActivity;

    protected static string $resource = TaxonomyResource::class;

    protected function getActions(): array
    {
        return [
            Action::make('create')
                ->label(trans('filament::resources/pages/create-record.form.actions.create.label'))
                ->action('create')
                ->keyBindings(['mod+s']),
        ];
    }

    protected function getFormActions(): array
    {
        return $this->getCachedActions();
    }

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(fn () => app(CreateTaxonomyAction::class)->execute(TaxonomyData::fromArray($data)));
    }
}
