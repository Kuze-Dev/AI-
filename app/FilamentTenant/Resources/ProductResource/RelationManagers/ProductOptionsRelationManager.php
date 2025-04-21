<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ProductResource\RelationManagers;

use Domain\Product\Models\ProductOption;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ProductOptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'productOptions';

    #[\Override]
    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->translateLabel()
                ->maxLength(100)
                ->required(),
            Forms\Components\Toggle::make('is_custom')
                ->label(
                    fn (bool $state) => $state
                        ? trans('Custom')
                        : trans('Regular')
                )
                ->default(false)
                ->helperText('Identify whether the option value in the form has customization.')
                ->visible(
                    function (?ProductOption $record) {
                        $productionOption = $this->ownerRecord->productOptions[0] ?? null;

                        if ($productionOption === null) {
                            return true;
                        }

                        return $productionOption->is($record);
                    }
                ),
        ]);
    }

    #[\Override]
    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->translateLabel()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_custom')
                    ->translateLabel()
                    ->searchable()
                    ->sortable()
                    ->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->translateLabel(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->defaultSort('id');
    }
}
