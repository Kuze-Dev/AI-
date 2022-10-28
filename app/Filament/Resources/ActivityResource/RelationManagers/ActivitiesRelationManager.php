<?php

declare(strict_types=1);

namespace App\Filament\Resources\ActivityResource\RelationManagers;

use App\Filament\Resources\ActivityResource;
use Exception;
use Filament\Resources\Table;
use Filament\Tables\Actions\ViewAction;

class ActivitiesRelationManager extends \AlexJustesen\FilamentSpatieLaravelActivitylog\RelationManagers\ActivitiesRelationManager
{
    /** @throws Exception */
    public static function table(Table $table): Table
    {
        return ActivityResource::table($table)
            ->actions([
                ViewAction::make(),
            ]);
    }
}
