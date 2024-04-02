<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ProductResource\RelationManagers;

// use App\FilamentTenant\Resources\ProductResource\Pages\EditProduct;
// use Domain\Product\Rules\UniqueProductSkuRule;
use Closure;
use Domain\Product\Enums\Status;
use Domain\Product\Models\ProductVariant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ProductVariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'productVariants';

    protected static ?string $recordTitleAttribute = 'Variant';

    #[\Override]
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Group::make()
                            ->schema(function ($state) {
                                $schemaArray = [];
                                foreach ($state['combination'] as $key => $combination) {
                                    $schemaArray[$key] =
                                        Forms\Components\TextInput::make("combination[{$key}].option_value")
                                            ->formatStateUsing(fn () => ucfirst($combination['option_value']))
                                            ->label(trans(ucfirst($combination['option'])))
                                            ->disabled();
                                }

                                return $schemaArray;
                            })->columns(2),
                        Forms\Components\Section::make('Inventory')
                            ->translateLabel()
                            ->schema([
                                Forms\Components\TextInput::make('sku')
                                    ->maxLength(100)
                                    // ->rule(function(EditProduct $livewire) {
                                    //     dump(func_get_args());
                                    // })
                                    // ->rule(fn (EditProduct $livewire) => new UniqueProductSkuRule($livewire)) // need to work on this
                                    ->required(),
                                Forms\Components\TextInput::make('stock')
                                    ->translateLabel()
                                    ->numeric()
                                    ->minValue(0)
                                    ->dehydrateStateUsing(fn ($state) => (int) $state),
                            ])->columns(2),
                        Forms\Components\Section::make('Pricing')
                            ->translateLabel()
                            ->schema([
                                Forms\Components\TextInput::make('retail_price')
                                    ->translateLabel()
                                    // Put custom rule to validate minimum value
                                    ->mask(fn (Forms\Components\TextInput\Mask $mask) => $mask->money(
                                        prefix: '$',
                                        thousandsSeparator: ',',
                                        decimalPlaces: 2,
                                        isSigned: false
                                    ))
                                    ->rules([
                                        function () {
                                            return function (string $attribute, mixed $value, Closure $fail) {
                                                if ($value <= 0) {
                                                    $fail("{$attribute} must be above zero.");
                                                }
                                            };
                                        },
                                    ])
                                    ->dehydrateStateUsing(fn ($state) => (float) $state)
                                    ->required(),

                                Forms\Components\TextInput::make('selling_price')
                                    ->translateLabel()
                                    // Put custom rule to validate minimum value
                                    ->mask(fn (Forms\Components\TextInput\Mask $mask) => $mask->money(
                                        prefix: '$',
                                        thousandsSeparator: ',',
                                        decimalPlaces: 2,
                                        isSigned: false
                                    ))
                                    ->rules([
                                        function () {
                                            return function (string $attribute, mixed $value, Closure $fail) {
                                                if ($value <= 0) {
                                                    $attributeName = ucfirst(explode('.', $attribute)[1]);
                                                    $fail("{$attributeName} must be above zero.");
                                                }
                                            };
                                        },
                                    ])
                                    ->dehydrateStateUsing(fn ($state) => (float) $state)
                                    ->required(),
                            ])->columns(2),
                        Forms\Components\Section::make('Status')
                            ->translateLabel()
                            ->schema([
                                Forms\Components\Toggle::make('status')
                                    ->label(
                                        fn ($state) => $state ? ucfirst(trans(STATUS::ACTIVE->value)) : ucfirst(trans(STATUS::INACTIVE->value))
                                    )
                                    ->helperText('This product variant will be hidden from all sales channels.'),
                            ])->columns(2),
                    ]),
            ])->columns(1);
    }

    #[\Override]
    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sku')
                    ->translateLabel()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stringCombination')
                    ->label(trans('Variation')),
                Tables\Columns\TextColumn::make('retail_price')
                    ->translateLabel()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('selling_price')
                    ->translateLabel()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock')
                    ->translateLabel()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->translateLabel()
                    ->formatStateUsing(fn ($state) => $state
                        ? ucfirst(STATUS::ACTIVE->value)
                        : ucfirst(Status::INACTIVE->value))
                    ->color(fn (ProductVariant $record) => $record->status ? 'success' : 'secondary')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('id', 'asc');
    }
}
