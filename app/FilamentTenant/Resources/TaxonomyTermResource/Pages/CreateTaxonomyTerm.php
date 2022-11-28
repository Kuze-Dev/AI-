<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\TaxonomyTermResource\Pages;

use App\FilamentTenant\Resources\TaxonomyTermResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTaxonomyTerm extends CreateRecord
{
    protected static string $resource = TaxonomyTermResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
