<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\TaxonomyResource\Pages;

use App\FilamentTenant\Resources\TaxonomyResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Exception;

class ListTaxonomies extends ListRecords
{
    protected static string $resource = TaxonomyResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
