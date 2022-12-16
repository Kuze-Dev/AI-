<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\TaxonomyResource\Pages;

use App\FilamentTenant\Resources\TaxonomyResource;
use Domain\Taxonomy\Actions\UpdateTaxonomyAction;
use Domain\Taxonomy\DataTransferObjects\TaxonomyData;
use Domain\Taxonomy\Models\Taxonomy;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditTaxonomy extends EditRecord
{
    protected static string $resource = TaxonomyResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /** @param Taxonomy $record */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(fn () => app(UpdateTaxonomyAction::class)->execute($record, new TaxonomyData(...$data)));
    }
}
