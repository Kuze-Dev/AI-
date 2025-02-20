<?php

declare(strict_types=1);

namespace App\Filament\Resources\AdminResource\RelationManagers;

use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use Exception;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

final class ActionsRelationManager extends RelationManager
{
    protected static string $relationship = 'actions';

    protected static ?string $recordTitleAttribute = 'id';

    /** @throws Exception */
    #[\Override]
    public function form(Form $form): Form
    {
        return ActivitiesRelationManager::form($form);
    }

    /** @throws Exception */
    #[\Override]
    public function table(Table $table): Table
    {
        return ActivitiesRelationManager::table($table);
    }

    #[\Override]
    protected function canCreate(): bool
    {
        return false;
    }

    #[\Override]
    protected function canEdit(Model $record): bool
    {
        return false;
    }

    #[\Override]
    protected function canDelete(Model $record): bool
    {
        return false;
    }
}
