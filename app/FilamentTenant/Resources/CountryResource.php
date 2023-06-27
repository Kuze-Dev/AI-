<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Domain\Address\Actions\DeleteCountryAction;
use Domain\Address\Models\Country;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Exception;
use Domain\Support\ConstraintsRelationships\Exceptions\DeleteRestrictedException;
use App\FilamentTenant\Resources\CountryResource\Pages;

class CountryResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = Country::class;

    protected static ?string $navigationGroup = 'eCommerce';

    protected static ?string $navigationIcon = 'heroicon-s-globe';

    protected static ?string $recordTitleAttribute = 'name';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

    // public static function form(Form $form): Form
    // {
    //     return $form->schema([
    //         Forms\Components\Card::make([
    //             Forms\Components\TextInput::make('code')
    //                 ->required(),
    //             Forms\Components\TextInput::make('name')
    //                 ->required(),
    //             Forms\Components\TextInput::make('capital'),
    //             Forms\Components\TextInput::make('timezone'),
    //             Forms\Components\TextInput::make('language'),
    //             Forms\Components\Toggle::make('active'),
    //         ]),
    //     ]);
    // }

    /** @throws Exception */
    /** @throws Exception */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')->label('Countries')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('capital')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('timezone')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('language')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('active')
                    ->toggleable()
                    ->enum([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ])
                    ->label('Active')
                    ->color(static function ($state): string {
                        if ($state == '1') {
                            return 'success';
                        }

                        return 'secondary';
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('active')
                    ->label('Active')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ]),
            ])

            ->actions([

                // Tables\Actions\EditAction::make(),
                // Tables\Actions\ActionGroup::make([
                //     Tables\Actions\DeleteAction::make()
                //         ->using(function (Country $record) {
                //             try {
                //                 return app(DeleteCountryAction::class)->execute($record);
                //             } catch (DeleteRestrictedException $e) {
                //                 return false;
                //             }
                //         }),
                // ]),
            ])
            ->bulkActions([])
            ->defaultSort('id', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCountry::route('/'),
            // 'create' => Pages\CreateCountry::route('/create'),
            // 'edit' => Pages\EditCountry::route('/{record}/edit'),
        ];
    }
}
