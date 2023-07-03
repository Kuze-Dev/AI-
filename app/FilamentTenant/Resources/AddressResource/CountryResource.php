<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\AddressResource;

use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Domain\Address\Models\Country;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Exception;
use App\FilamentTenant\Resources\AddressResource\CountryResource\Pages;

class CountryResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = Country::class;

    protected static ?string $navigationGroup = 'eCommerce';

    protected static ?string $navigationIcon = 'heroicon-s-globe';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

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
                Tables\Columns\ToggleColumn::make('active')->label('Active'),
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
                Tables\Actions\ViewAction::make()->url(function (Country $record) {
                    return StateResource::getUrl().'?'.http_build_query(['tableFilters' => ['country_id' => ['value' => $record->getKey()]]]);
                }),

            ])
            ->bulkActions([])
            ->defaultSort('id', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCountry::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
