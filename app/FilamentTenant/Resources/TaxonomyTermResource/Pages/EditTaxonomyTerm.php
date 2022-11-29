<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\TaxonomyTermResource\Pages;

use App\FilamentTenant\Resources\TaxonomyTermResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTaxonomyTerm extends EditRecord
{
    protected static string $resource = TaxonomyTermResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
