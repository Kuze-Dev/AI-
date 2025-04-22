<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\CountryResource\Pages;

use App\FilamentTenant\Resources\CountryResource;
use Filament\Resources\Pages\ListRecords;

class ListCountry extends ListRecords
{
    protected static string $resource = CountryResource::class;
}
