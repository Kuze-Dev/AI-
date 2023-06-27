<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Domain\Currency\Models\Currency;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Exception;
use App\FilamentTenant\Resources\CurrencyResource\Pages;

class CurrencyResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = Currency::class;

    protected static ?string $navigationGroup = 'eCommerce';

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $recordTitleAttribute = 'name';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['code'];
    }

    /** @throws Exception */
    /** @throws Exception */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('code')
                    ->label('Currency')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('exchange_rate')
                    ->label('Exchange Rate')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),

                Tables\Columns\ToggleColumn::make('enabled')->label('status'),

                Tables\Columns\BadgeColumn::make('default')
                    ->enum([
                        '1' => 'Selected',
                        '0' => 'Not Selected',
                    ])
                    ->color(static function ($state): string {
                        if ($state == '1') {
                            return 'success';
                        }

                        return 'secondary';
                    })->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->sortable()
                    ->toggleable(),

            ])

            ->filters([
                Tables\Filters\SelectFilter::make('enabled')
                    ->label('Status')
                    ->options([
                        '1' => 'Selected',
                        '0' => 'Not Selected',
                    ]),
            ])
            ->actions([
            ])
            ->bulkActions([])
            ->defaultSort('id', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCurrency::route('/'),
        ];
    }
}
