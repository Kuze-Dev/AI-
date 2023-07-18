<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ReviewResource\RelationManagers;

use App\FilamentTenant\Resources\ReviewResource;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;


class ReviewRelationManager extends RelationManager
{
    protected static string $relationship = 'reviews';

    protected static ?string $recordTitleAttribute = 'comment';


    public static function form(Form $form): Form
    {
        return ReviewResource::form($form);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->columns([
            Tables\Columns\TextColumn::make('rating')
            ->label('Rating')
            ->sortable(),
            Tables\Columns\TextColumn::make('comment')
                ->label('Comment')
                ->sortable(),
        ])
        ->actions([
            Tables\Actions\ViewAction::make(),
        ])
        ->bulkActions([])
        ->defaultSort('id', 'asc');
    }

}
