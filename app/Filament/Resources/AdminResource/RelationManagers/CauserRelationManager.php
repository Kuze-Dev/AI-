<?php

declare(strict_types=1);

namespace App\Filament\Resources\AdminResource\RelationManagers;

use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use Exception;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Illuminate\Database\Eloquent\Model;

final class CauserRelationManager extends RelationManager
{
    protected static string $relationship = 'causerActivities';

    protected static ?string $recordTitleAttribute = 'id';

    /** @throws Exception */
    public static function form(Form $form): Form
    {
        return ActivitiesRelationManager::form($form);
    }

    /** @throws Exception */
    public static function table(Table $table): Table
    {
        return ActivitiesRelationManager::table($table);
    }

    protected function canCreate(): bool
    {
        return false;
    }

    protected function canEdit(Model $record): bool
    {
        return false;
    }

    protected function canDelete(Model $record): bool
    {
        return false;
    }
}
