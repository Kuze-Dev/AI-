<?php

declare(strict_types=1);

namespace App\Filament\Resources;

class ActivityResource extends \AlexJustesen\FilamentSpatieLaravelActivitylog\Resources\ActivityResource
{
    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return trans('System');
    }
}
