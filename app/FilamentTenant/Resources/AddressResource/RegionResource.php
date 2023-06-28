<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\AddressResource;

use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Domain\Address\Models\Country;
use Domain\Address\Models\Region;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Exception;
use App\FilamentTenant\Resources\AddressResource\RegionResource\Pages;

class RegionResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = Region::class;

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

                Tables\Columns\TextColumn::make('name')->label('Regions')
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
                Tables\Filters\SelectFilter::make('country_id')
                    ->label('Country')
                    ->options(function () {
                        $countries = Country::all();

                        return $countries->pluck('name', 'id')->toArray();
                    }),
            ])

            ->actions([
                Tables\Actions\ViewAction::make()->url(function (Region $record) {
                    return "/admin/cities?tableFilters[region_id][value]={$record->id}";

                }),

            ])
            ->bulkActions([])
            ->defaultSort('id', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRegion::route('/'),
        ];
    }
}
