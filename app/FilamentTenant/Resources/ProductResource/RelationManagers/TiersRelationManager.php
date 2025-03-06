<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ProductResource\RelationManagers;

use Domain\Product\Enums\DiscountAmountType;
use Domain\Tier\Models\Tier;
use Exception;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Contracts\HasRelationshipTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class TiersRelationManager extends RelationManager
{
    protected static string $relationship = 'tiers';

    protected static ?string $inverseRelationship = 'products';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $title = 'Tier Discounts';

    /** @throws Exception */
    #[\Override]
    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->translateLabel(),
                Tables\Columns\TextColumn::make('discount_amount_type')
                    ->translateLabel()
                    ->formatStateUsing(fn ($state) => $state === DiscountAmountType::PERCENTAGE->value
                        ? 'Percentage' : 'Fixed Value'),
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
                            ->translateLabel()
                            ->placeholder(trans('Select tier')),
                        Radio::make('discount_amount_type')->options([
                            DiscountAmountType::PERCENTAGE->value => ucwords(str_replace('_', ' ', DiscountAmountType::PERCENTAGE->value)),
                            DiscountAmountType::FIXED_VALUE->value => ucwords(str_replace('_', ' ', DiscountAmountType::FIXED_VALUE->value)),
                        ])
                            ->reactive()
                            ->required()
                            ->filled()
                            ->label(trans('Amount Type')),
                        TextInput::make('discount')
                            ->translateLabel()
                            // ->mask(
                            //     fn (TextInput\Mask $mask) => $mask
                            //         ->numeric()
                            //         ->decimalPlaces(3)
                            //         ->decimalSeparator('.')
                            //         ->minValue(0)
                            //         ->maxValue(100)
                            // )
                            ->required(),
                    ])
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['id', 'name'])
                    ->using(fn ($livewire, array $data): ?Model =>
                        $livewire->getRelationship()
                            ->attach(
                                $data['recordId'],
                                [
                                    'discount_amount_type' => $data['discount_amount_type'],
                                    'discount' => $data['discount'],
                                ]
                            )),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->translateLabel()
                    ->form(fn (): array => [
                        Radio::make('discount_amount_type')->options([
                            DiscountAmountType::FIXED_VALUE->value => 'Fixed Value',
                            DiscountAmountType::PERCENTAGE->value => 'Percentage',
                        ])
                            ->reactive()
                            ->required()
                            ->default(DiscountAmountType::PERCENTAGE->value)
                            ->filled()
                            ->label(trans('Amount Type')),
                        TextInput::make('discount')
                            ->label(trans('Discount (%)'))
                            // ->mask(
                            //     fn (TextInput\Mask $mask) => $mask
                            //         ->numeric()
                            //         ->decimalPlaces(3)
                            //         ->decimalSeparator('.')
                            //         ->minValue(0)
                            //         ->maxValue(100)
                            // )
                            ->required(),
                    ])
                    ->using(function (Tier $record, array $data): Tier {
                        $record->products()->update($data);

                        return $record;
                    }),
                Tables\Actions\DetachAction::make()
                    ->label(trans('Detach Discount')),
            ]);
    }
}
