<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ProductResource\RelationManagers;

use Domain\Tier\Models\Tier;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Exception;
use Filament\Forms\Components\TextInput;
use Filament\Tables;
use Filament\Tables\Contracts\HasRelationshipTable;
use Illuminate\Database\Eloquent\Model;

class TiersRelationManager extends RelationManager
{
    protected static string $relationship = 'tiers';
    protected static ?string $inverseRelationship = 'products';
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $title = 'Tier Discounts';

    /** @throws Exception */
    public static function table(Table $table): Table
    {
        $tiers = Tier::all();

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->translateLabel(),
                Tables\Columns\TextColumn::make('discount')
                    ->translateLabel()
                    ->formatStateUsing(fn (string $state) => floatval($state)),

            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label(trans('Attach Tier Discount'))
                    ->modalHeading(trans('Attach Tier Discount'))
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->translateLabel(),
                        TextInput::make('discount')
                            ->label(trans('Discount (%)'))
                            ->mask(
                                fn (TextInput\Mask $mask) => $mask
                                    ->numeric()
                                    ->decimalPlaces(3)
                                    ->decimalSeparator('.')
                                    ->minValue(0)
                                    ->maxValue(100)
                            )
                            ->required(),
                    ])
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['id', 'name'])
                    ->using(function (HasRelationshipTable $livewire, array $data): Model|null {
                        /** @phpstan-ignore-next-line */
                        return $livewire->getRelationship()
                            ->attach(
                                $data['recordId'],
                                ['discount' => $data['discount']]
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->translateLabel()
                    ->form(fn (): array => [
                        TextInput::make('discount')
                            ->label(trans('Discount (%)'))
                            ->mask(
                                fn (TextInput\Mask $mask) => $mask
                                    ->numeric()
                                    ->decimalPlaces(3)
                                    ->decimalSeparator('.')
                                    ->minValue(0)
                                    ->maxValue(100)
                            )
                            ->required(),
                    ])
                    ->using(function (Model $record, array $data): Model {
                        /** @phpstan-ignore-next-line */
                        $record->products()->update($data);

                        return $record;
                    }),
                Tables\Actions\DetachAction::make()
                    ->label(trans('Detach Discount')),
            ]);
    }
}
