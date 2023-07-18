<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Domain\Review\Models\Review;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Tables;
use Exception;
use App\FilamentTenant\Resources\ReviewResource\Pages;

class ReviewResource extends Resource
{
    use ContextualResource;
    protected static ?string $model = Review::class;

    protected static ?string $recordTitleAttribute = 'name';

    /** @throws Exception */

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Fieldset::make('customer')->relationship('customer')->schema([
                    Forms\Components\TextInput::make('first_name'),
                    Forms\Components\TextInput::make('email'),
                ]),
                Forms\Components\TextInput::make('comment'),
                Forms\Components\TextInput::make('rating'),
            ])->columns(1);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('comment')
                    ->label('Review')
                    ->sortable()
                    ->searchable(),
                // Tables\Columns\TextColumn::make('Customer.first_name')
                //     ->label('Name')
                //     ->sortable()
                //     ->toggleable()
                //     ->searchable(),
            ])
            ->actions([])
            ->bulkActions([])
            ->defaultSort('id', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReview::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}