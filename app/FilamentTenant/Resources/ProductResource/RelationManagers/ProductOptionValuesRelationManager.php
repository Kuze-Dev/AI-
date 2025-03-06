<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ProductResource\RelationManagers;

use App\Features\ECommerce\ColorPallete;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductOption;
use Domain\Product\Models\ProductOptionValue;
use Domain\Product\Models\ProductVariant;
use Domain\Tenant\TenantFeatureSupport;
use Exception;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rules\Unique;
use Throwable;

/**
 * @property-read Product $ownerRecord
 */
class ProductOptionValuesRelationManager extends RelationManager
{
    protected static string $relationship = 'productOptionValues';

    #[\Override]
    public function form(Form $form): Form
    {
        $isProductOptionCustom = fn (Forms\Get $get): bool => once(
            function () use ($get) {

                if ($get('productOption') === null) {
                    return false;
                }

                return ProductOption::whereKey($get('productOption'))
                    ->select(['id', 'is_custom'])
                    ->value('is_custom');
            }
        );

        $hasColorPallet = fn (Forms\Get $get): bool => once(function () use ($isProductOptionCustom, $get) {
            // visible if enabled ang color pallet FEATURE flag
            // visible if true ang is_custom ng selected product_option

            if (TenantFeatureSupport::inactive(ColorPallete::class)) {
                return false;
            }

            if (
                $get('icon_type') === 'color_palette' &&
                $get('productOption')
            ) {
                return $isProductOptionCustom($get);
            }

            return false;
        });

        return $form->schema([

            Forms\Components\Select::make('productOption')
                ->translateLabel()
                ->relationship(
                    titleAttribute: 'name',
                    modifyQueryUsing: fn (Builder $query) => $query->whereBelongsTo($this->ownerRecord)
                )
                ->searchable()
                ->preload()
                ->required()
                ->reactive()
                ->columnSpanFull(),

            Forms\Components\TextInput::make('name')
                ->translateLabel()
                ->required()
                ->maxLength(100)
                ->unique(
                    ignoreRecord: true,
                    modifyRuleUsing: fn (Unique $rule, Forms\Get $get) => $rule
                        ->where(
                            'product_option_id',
                            $get('productOption')
                        )
                )
                ->columnSpanFull(),

            Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Select::make('icon_type')
                        ->translateLabel()
                        ->default('text')
                        ->required()
                        ->options(
                            fn () => TenantFeatureSupport::active(ColorPallete::class)
                                ? [
                                    'text' => 'Text',
                                    'color_palette' => 'Color Palette',
                                ]
                                : [
                                    'text' => 'Text',
                                ]
                        )
                        ->columnSpan(
                            fn (Forms\Get $get) => $hasColorPallet($get) ? 1 : 2
                        )
                        ->visible($isProductOptionCustom(...))
                        ->reactive(),

                    Forms\Components\ColorPicker::make('icon_value')
                        ->label(trans('Icon Value (HEX)'))
                        ->visible($hasColorPallet(...)),
                ])
                ->columns()
                ->columnSpanFull(),
        ])
            ->columns();
    }

    #[\Override]
    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('productOption.name')
                    ->label(trans('Option name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label(trans('Option value'))
                    ->searchable([(new ProductOptionValue())->qualifyColumn('name')])
                    ->sortable(),
                Tables\Columns\TextColumn::make('icon_details')
                    ->translateLabel(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->translateLabel()
                    ->mutateRecordDataUsing(function (array $data): array {

                        $data['icon_type'] = $data['data']['icon_type'] ?? null;
                        $data['icon_value'] = $data['data']['icon_value'] ?? null;

                        return $data;
                    })
                    ->using($this->update(...)),
                Tables\Actions\DeleteAction::make()
                    ->translateLabel()
                    ->successNotificationTitle(trans('Option value has been removed.'))
                    ->using(function (ProductOptionValue $record, Tables\Actions\Action $action): bool {

                        //                        if (! $record->productOption instanceof ProductOption) {
                        //                            $action
                        //                                ->failureNotificationTitle(trans('The option value is unlinked from an option.'));
                        //
                        //                            return false;
                        //                        }

                        try {

                            ProductVariant::where('product_id', $record->productOption->product_id)
                                ->where(function (Builder $query) use ($record) {
                                    $query->whereJsonContains('combination', [['option_value_id' => $record->id]]);
                                })->delete();

                            $record->delete();

                        } catch (Exception) {
                            $action->failureNotificationTitle(trans('Failed to remove Option value.'));

                            return false;
                        }

                        return true;
                    })
                    ->authorize('delete'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->translateLabel()
                    ->using(function (Collection $records): void {
                        foreach ($records as $record) {
                            if (! isset($record->productOption)) {
                                return;
                            }

                            ProductVariant::whereBelongsTo($record->productOption->product)
                                ->where(function (Builder $query) use ($record) {
                                    $query->whereJsonContains('combination', [['option_value_id' => $record->getKey()]]);
                                })
                                ->delete();

                            $record->delete();
                        }
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->translateLabel()
                    ->using($this->create(...)),
            ])
            ->defaultSort('id');
    }

    private function create(array $data, Tables\Actions\CreateAction $action): ProductOptionValue
    {
        /** @var ProductOptionValue $productOptionValue */
        $productOptionValue = ProductOptionValue::create([
            'product_option_id' => $data['productOption'],
            'name' => $data['name'],
            'data' => isset($data['icon_type'])
                ? [
                    'icon_type' => $data['icon_type'],
                    'icon_value' => $data['icon_value'] ?? '',
                ]
                : [
                    'icon_type' => 'text',
                    'icon_value' => '',
                ],
        ]);

        /** @var ProductOption|null $ownerRecordProductOption */
        $ownerRecordProductOption = $this->ownerRecord
            ->productOptions
            ->firstWhere('id', '!=', $data['productOption']);

        if (
            $ownerRecordProductOption === null ||
            $ownerRecordProductOption->productOptionValues->isEmpty()
        ) {
            ProductVariant::create([
                'product_id' => $this->ownerRecord->id,
                'sku' => $this->ownerRecord->sku.$productOptionValue->id,
                'combination' => [
                    [
                        'option' => $productOptionValue->productOption->name,
                        'option_id' => $productOptionValue->productOption->id,
                        'option_value' => $productOptionValue->name,
                        'option_value_id' => $productOptionValue->id,
                    ],
                ],
                'retail_price' => $this->ownerRecord->retail_price,
                'selling_price' => $this->ownerRecord->selling_price,
                'stock' => $this->ownerRecord->stock,
                'status' => $this->ownerRecord->status,
            ]);

            return $productOptionValue;
        }

        // Remove variants with combination having only one array element
        ProductVariant::whereBelongsTo($this->ownerRecord)
            ->where(function (Builder $query) use ($productOptionValue, $ownerRecordProductOption) {
                $query->whereJsonContains('combination', [['option_id' => $productOptionValue->product_option_id]])
                    ->orWhereJsonContains('combination', [['option_id' => $ownerRecordProductOption->id]]);
            })
            ->get()
            ->each(function (ProductVariant $productVariant) use ($action) {
                try {
                    if (count($productVariant->combination) === 1) {
                        $productVariant->delete();
                    }
                } catch (Throwable $e) {

                    $action->failureNotificationTitle($e->getMessage())->failure();
                    $action->halt(shouldRollBackDatabaseTransaction: true);
                }
            });

        /**
         * Sync product variants connected to this option value
         */
        foreach ($ownerRecordProductOption->productOptionValues as $pairOptionValue) {
            try {
                ProductVariant::create([
                    'product_id' => $this->ownerRecord->id,
                    'sku' => $this->ownerRecord->sku.$productOptionValue->id.$pairOptionValue->id,
                    'combination' => [
                        [
                            'option' => $productOptionValue->productOption->name,
                            'option_id' => $productOptionValue->productOption->id,
                            'option_value' => $productOptionValue->name,
                            'option_value_id' => $productOptionValue->id,
                        ],
                        [
                            'option' => $ownerRecordProductOption->name,
                            'option_id' => $ownerRecordProductOption->id,
                            'option_value' => $pairOptionValue->name,
                            'option_value_id' => $pairOptionValue->id,
                        ],
                    ],
                    'retail_price' => $this->ownerRecord->retail_price,
                    'selling_price' => $this->ownerRecord->selling_price,
                    'stock' => $this->ownerRecord->stock,
                    'status' => $this->ownerRecord->status,
                ]);
            } catch (Throwable $e) {

                $action->failureNotificationTitle($e->getMessage())->failure();
                $action->halt(shouldRollBackDatabaseTransaction: true);
            }
        }

        return $productOptionValue;

    }

    private function update(ProductOptionValue $record, array $data, Tables\Actions\EditAction $action): void
    {

        $record->update([
            'name' => $data['name'],
            'data' => isset($data['icon_type'])
                ? [
                    'icon_type' => $data['icon_type'],
                    'icon_value' => $data['icon_value'] ?? '',
                ]
                : [
                    'icon_type' => 'text',
                    'icon_value' => '',
                ],
        ]);

        // Sync product variants connected to this option value
        foreach (
            ProductVariant::whereBelongsTo($record->productOption->product)
                ->where(fn (Builder $query) => $query
                    ->whereJsonContains('combination', [
                        ['option_value_id' => $record->id],
                    ]))
                ->get() as $productVariant
        ) {
            try {
                $combinations = [];
                foreach ($productVariant->combination as $key => $item) {
                    if ($item['option_value_id'] === $record->id) {
                        $item['option_value'] = $data['name'];
                    }
                    $combinations[$key] = $item;
                }

                $productVariant->combination = $combinations;
                $productVariant->save();
            } catch (Throwable $e) {
                $action->failureNotificationTitle($e->getMessage())
                    ->failure();
                $action->halt(shouldRollBackDatabaseTransaction: true);
            }
        }

    }
}
