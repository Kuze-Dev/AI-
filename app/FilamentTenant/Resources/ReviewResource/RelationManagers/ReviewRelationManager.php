<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ReviewResource\RelationManagers;

use Filament\Resources\Form;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;


class ReviewRelationManager extends RelationManager
{
    protected static string $relationship = 'reviews';

    protected static ?string $recordTitleAttribute = 'comment';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Fieldset::make('customer')->relationship('customer')->schema([
                    Forms\Components\TextInput::make('first_name')->default('Anonymous'),
                    Forms\Components\TextInput::make('email')->default('Anonymous'),
                ]),
                Forms\Components\TextInput::make('comment'),
                Forms\Components\TextInput::make('rating'),
            ])->columns(1);
    }
    public static function table(Table $table): Table
    {
        return $table
        ->columns([
            Tables\Columns\TextColumn::make('Customer.first_name')->default('Anonymous')
            ->label('Name')
            ->sortable(),
            Tables\Columns\TextColumn::make('rating')
            ->label('Rating')
            ->sortable(),
            Tables\Columns\TextColumn::make('comment')
            ->label('Comment') 
            ->sortable(),
     
        ])
        ->actions([
           Tables\Actions\ViewAction::make(),   
           Tables\Actions\DeleteAction::make()
        ])
        ->bulkActions([])
        ->defaultSort('id', 'asc');
    }

}
