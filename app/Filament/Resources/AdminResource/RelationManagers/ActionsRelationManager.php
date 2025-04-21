<?php

declare(strict_types=1);

namespace App\Filament\Resources\AdminResource\RelationManagers;

use App\Filament\Resources\ActivityResource;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ActionsRelationManager extends RelationManager
{
    protected static string $relationship = 'actions';

    protected static ?string $recordTitleAttribute = 'id';

    #[\Override]
    public function infolist(Infolist $infolist): Infolist
    {
        return ActivityResource::infolist($infolist);
    }

    //    public function form(Form $form): Form
    //    {
    //        return ActivityResource::form($form);
    //    }

    #[\Override]
    public function table(Table $table): Table
    {
        return ActivityResource::table($table)
            ->filtersLayout(FiltersLayout::AboveContentCollapsible);
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
