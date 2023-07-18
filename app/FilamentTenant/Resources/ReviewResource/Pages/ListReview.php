<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ReviewResource\Pages;

use App\FilamentTenant\Resources\ReviewResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReview extends ListRecords
{
    protected static string $resource = ReviewResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
