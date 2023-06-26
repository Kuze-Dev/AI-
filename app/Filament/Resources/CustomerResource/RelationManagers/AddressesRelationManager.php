<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use Domain\Customer\Actions\DeleteAddressAction;
use Domain\Customer\Models\Address;
use Domain\Support\ConstraintsRelationships\Exceptions\DeleteRestrictedException;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Exception;

class AddressesRelationManager extends RelationManager
{
    protected static string $relationship = 'addresses';

    protected static ?string $recordTitleAttribute = 'full_detail';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('address_line_1')
                    ->translateLabel()
                    ->required()
                    ->string()
                    ->maxLength(255),
                Forms\Components\TextInput::make('address_line_2')
                    ->translateLabel()
                    ->nullable()
                    ->string()
                    ->maxLength(255),
                Forms\Components\TextInput::make('country')  // TODO: relation
                    ->translateLabel()
                    ->required()
                    ->string()
                    ->maxLength(255),

                Forms\Components\TextInput::make('state_or_region') // TODO: relation
                    ->translateLabel()
                    ->nullable()
                    ->string()
                    ->maxLength(255),

                Forms\Components\TextInput::make('city_or_province') // TODO: relation
                    ->translateLabel()
                    ->required()
                    ->string()
                    ->maxLength(255),
                Forms\Components\TextInput::make('zip_code')
                    ->translateLabel()
                    ->required()
                    ->string()
                    ->maxLength(255),

                Forms\Components\Checkbox::make('is_default_billing')
                    ->translateLabel(),
                Forms\Components\Checkbox::make('is_default_shipping')
                    ->translateLabel(),

            ])->columns(1);
    }

    /** @throws Exception */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('address_line_1')
                    ->sortable()
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('address_line_2')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrap(),
                Tables\Columns\TextColumn::make('country') // TODO: relation
                    ->sortable()
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('state') // TODO: relation
                    ->sortable()
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('region') // TODO: relation
                    ->sortable()
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('province') // TODO: relation
                    ->sortable()
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('zip_code')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrap(),
                Tables\Columns\IconColumn::make('is_default_billing')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_default_shipping')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_default_billing'),
                Tables\Filters\TernaryFilter::make('is_default_shipping'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DeleteAction::make()
                        ->using(function (Address $record) {
                            try {
                                return app(DeleteAddressAction::class)->execute($record);
                            } catch (DeleteRestrictedException $e) {
                                return false;
                            }
                        }),
                ]),
            ]);
    }
}
