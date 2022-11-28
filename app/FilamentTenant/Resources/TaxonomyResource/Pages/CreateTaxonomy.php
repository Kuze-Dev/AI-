<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\TaxonomyResource\Pages;

use App\FilamentTenant\Resources\TaxonomyResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTaxonomy extends CreateRecord
{
    protected static string $resource = TaxonomyResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
