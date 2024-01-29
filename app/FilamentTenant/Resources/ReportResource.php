<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\FilamentTenant\Resources\ReportResource\Pages;
use Filament\Resources\Resource;

class ReportResource extends Resource
{
    public static function getNavigationGroup(): ?string
    {
        return trans('eCommerce');
    }

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReport::route('/'),
        ];
    }
}
