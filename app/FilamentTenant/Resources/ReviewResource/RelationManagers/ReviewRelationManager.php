<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ReviewResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ReviewRelationManager extends RelationManager
{
    protected static string $relationship = 'reviews';

    protected static ?string $recordTitleAttribute = 'comment';

    #[\Override]
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Fieldset::make('customer')->schema([
                    Forms\Components\TextInput::make('customer_name')->label('Name')->default('Anonymous'),
                    Forms\Components\TextInput::make('customer_email')->label('email')->default('Anonymous'),
                ]),
                Forms\Components\TextInput::make('comment'),
                Forms\Components\TextInput::make('rating'),
            ])->columns(1);
    }

    #[\Override]
    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer_name')->default('Anonymous')
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
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('id', 'asc');
    }
}
