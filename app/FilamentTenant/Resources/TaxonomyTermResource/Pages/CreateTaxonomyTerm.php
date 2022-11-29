<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\TaxonomyTermResource\Pages;

use App\FilamentTenant\Resources\TaxonomyResource;
use Domain\Taxonomy\Actions\CreateTaxonomyTermAction;
use Domain\Taxonomy\DataTransferObjects\TaxonomyTermData;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateTaxonomyTerm extends CreateRecord
{
    protected static string $resource = TaxonomyResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(
            fn () => app(CreateTaxonomyTermAction::class)
                ->execute(new TaxonomyTermData(
                    taxonomy_id: (int) $data['taxonomy_id'],
                    name:$data['name'],
                    slug:$data['slug']
                ))
        );
    }
}
