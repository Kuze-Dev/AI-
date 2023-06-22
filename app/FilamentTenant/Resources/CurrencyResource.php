<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Domain\Currency\Models\Currency;
use Domain\Currency\Actions\DeleteCurrencyAction;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Filters\Layout;
use Exception;
use Domain\Support\ConstraintsRelationships\Exceptions\DeleteRestrictedException;
use App\FilamentTenant\Resources\CurrencyResource\Pages;

class CurrencyResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = Currency::class;

    protected static ?string $navigationGroup = 'eCommerce';

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['code'];
    }
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Card::make([
                Forms\Components\TextInput::make('code')
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\Toggle::make('enabled'),
                Forms\Components\TextInput::make('exchange_rate'),
                Forms\Components\Toggle::make('default'),


            ]),
        ]);
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

                Tables\Columns\BadgeColumn::make('enabled')
                    ->enum([
                        '1' => 'Selected',
                        '0' => 'Not Selected',
                    ])
                    ->label('Status')
                    ->color(static function ($state): string {
                        if ($state == '1') {
                            return 'success';
                        }

                        return 'secondary';
                    }),

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
                    ->toggleable()


            ])

            ->filters([
                Tables\Filters\SelectFilter::make('enabled')
                    ->label('Status')
                    ->options([
                        '' => 'All',
                        '1' => 'Selected',
                        '0' => 'Not Selected',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DeleteAction::make()
                        ->using(function (Currency $record) {
                            try {
                                return app(DeleteCurrencyAction::class)->execute($record);
                            } catch (DeleteRestrictedException $e) {
                                return false;
                            }
                        }),
                ]),
            ])
            ->bulkActions([])
            ->defaultSort('id', 'asc');
    }






    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCurrency::route('/'),
            'create' => Pages\CreateCurrency::route('/create'),
            'edit' => Pages\EditCurrency::route('/{record}/edit'),
        ];
    }
}