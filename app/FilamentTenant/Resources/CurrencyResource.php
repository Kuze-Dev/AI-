<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\FilamentTenant\Resources\CurrencyResource\Pages;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Domain\Currency\Actions\UpdateCurrencyEnabledAction;
use Domain\Currency\Models\Currency;
use Exception;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class CurrencyResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = Currency::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string
    {
        return trans('eCommerce');
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['code'];
    }

    /** @throws Exception */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Currency')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('symbol')
                    ->label('Symbol')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\ToggleColumn::make('enabled')->label('status')->disabled(function (Currency $record) {
                    return $record->enabled;
                })
                    ->updateStateUsing(function (Currency $record) {
                        return app(UpdateCurrencyEnabledAction::class)->execute($record);
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('enabled')
                    ->label('Status')
                    ->options([
                        '1' => 'Selected',
                        '0' => 'Not Selected',
                    ]),
            ])
            ->actions([Tables\Actions\EditAction::make()
                ->label('Edit Status')
                ->modalHeading('Change enabled currency?')
                ->requiresConfirmation()->action(function (Currency $record) {
                    return app(UpdateCurrencyEnabledAction::class)->execute($record);
                })])
            ->bulkActions([])
            ->defaultSort('id', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCurrency::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
