<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\TaxonomyTermResource\Pages;

use App\FilamentTenant\Resources\TaxonomyTermResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTaxonomyTerms extends ListRecords
{
    protected static string $resource = TaxonomyTermResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
