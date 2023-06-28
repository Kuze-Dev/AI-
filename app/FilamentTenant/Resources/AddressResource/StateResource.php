<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\AddressResource;

use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Domain\Address\Models\Country;
use Domain\Address\Models\State;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Exception;
use App\FilamentTenant\Resources\AddressResource\StateResource\Pages;

class StateResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = State::class;

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

                Tables\Columns\TextColumn::make('name')->label('States')
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
                Tables\Actions\ViewAction::make()->url(function (State $record) {
                    return "/admin/cities?tableFilters[state_id][value]={$record->id}";

                }),

            ])
            ->bulkActions([])
            ->defaultSort('id', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListState::route('/'),
        ];
    }
}
