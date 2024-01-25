<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\FilamentTenant\Resources\ReportResource\Pages;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Filament\Resources\Resource;

class ReportResource extends Resource
{

    protected static ?string $navigationGroup = 'eCommerce';

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReport::route('/'),
        ];
    }
}
