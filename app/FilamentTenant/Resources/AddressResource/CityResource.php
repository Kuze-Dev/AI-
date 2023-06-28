<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\AddressResource;

use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Domain\Address\Models\City;
use Domain\Address\Models\Region;
use Domain\Address\Models\State;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Exception;
use App\FilamentTenant\Resources\AddressResource\CityResource\Pages;

class CityResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = City::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $recordTitleAttribute = 'name';

    public static function canCreate(): bool
    {
        return false;
    }

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

                Tables\Columns\TextColumn::make('name')->label('Cities')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('region_id')
                    ->label('Region')
                    ->options(function (City $record) {
                        $region = Region::all();

                        return $region->pluck('name', 'id')->toArray();
                    }),

                Tables\Filters\SelectFilter::make('state_id')
                    ->label('State')
                    ->options(function (City $record) {
                        $state = State::all();

                        return $state->pluck('name', 'id')->toArray();
                    }),

            ])
            ->actions([
            ])
            ->bulkActions([])
            ->defaultSort('id', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCity::route('/'),
        ];
    }
}
