<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ProductResource\RelationManagers;

use App\Features\ECommerce\ColorPallete;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;

class OptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'productOptionValues';

    protected static ?string $recordTitleAttribute = 'Product Option Values';

    public static function form(Form $form): Form
    {
        // Temporarily commented
        return $form->schema([
            \Filament\Forms\Components\TextInput::make('name')
                ->translateLabel()
                ->maxLength(100)
                ->lazy()
                ->columnSpan(2)
                // ->columnSpan(
                //     fn (Closure $get) => $get('../../is_custom') ? 1 : 2
                // )
                ->required(),
            // \Filament\Forms\Components\Select::make('icon_type')
            //     ->default('text')
            //     ->required()
            //     ->options(fn () => tenancy()->tenant?->features()->active(ColorPallete::class) ? [
            //         'text' => 'Text',
            //         'color_palette' => 'Color Palette',
            //     ] : [
            //         'text' => 'Text',
            //     ]),

            // \Filament\Forms\Components\ColorPicker::make('icon_value')
            //     ->label(trans('Icon Value (HEX)')),
            // ->hidden(fn (Closure $get) => !($get('icon_type') === 'color_palette' && $get('../../is_custom'))),
            // ->hidden(fn (Closure $get) => !$get('../../is_custom'))
            // ->reactive(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product_option_name')
                    ->label(trans('Option Name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label(trans('Option Value'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('iconDetails')
                    ->translateLabel()
                    ->searchable()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('id', 'asc');
    }
}
