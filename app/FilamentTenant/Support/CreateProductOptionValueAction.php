<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support;

use App\Features\ECommerce\ColorPallete;
use Closure;
use Domain\Product\Models\ProductOption;
use Domain\Product\Models\ProductOptionValue;
use Domain\Product\Models\ProductVariant;
use Exception;
use Filament\Tables;
use Filament\Tables\Contracts\HasRelationshipTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateProductOptionValueAction
{
    public static function proceed(): Tables\Actions\CreateAction
    {
        return Tables\Actions\CreateAction::make()
            ->translateLabel()
            ->using(
                function (HasRelationshipTable $livewire, array $data, Tables\Actions\Action $action): Model|string {
                    return self::processCreate($livewire, $data, $action);
                }
            )
            ->form(fn (): array => self::getFormElements());
    }

    protected static function processCreate(HasRelationshipTable $livewire, array $data, Tables\Actions\Action $action): Model|string
    {
        $duplicateOptionValue = ProductOptionValue::select('id')
            ->whereProductOptionId($data['product_option_id'])
            ->whereName($data['name'])
            ->first();

        if ($duplicateOptionValue) {
            $action->failureNotificationTitle(trans('Option value name has duplicate.'))
                ->failure();

            return $action->halt();
        }

        return DB::transaction(function () use ($livewire, $data) {
            try {
                DB::beginTransaction();

                if (!isset($data['icon_type'])) {
                    $data['data'] = ['icon_type' => 'text', 'icon_value' => ''];
                } else {
                    $data['data'] = ['icon_type' => $data['icon_type'], 'icon_value' => $data['icon_value'] ?? ''];
                }

                if (! $livewire instanceof HasRelationshipTable) {
                    return 'Error: livewire has on instance of HasRelationshipTable';
                }
                
                $optionValueModel = $livewire->getRelationship()->create($data);

                // Add product variants
                $pairOption = $livewire->ownerRecord->productOptions->where('id', '!=', $data['product_option_id'])->first();

                if ($pairOption && count($pairOption->productOptionValues->toArray())) {
                    // Remove variants with combination having only one array element
                    $productVariants = ProductVariant::where('product_id', $livewire->ownerRecord->id)
                        ->where(function (Builder $query) use ($optionValueModel, $livewire) {
                            $query->whereJsonContains('combination', [['option_id' => (int) $optionValueModel->product_option_id]])
                                ->orWhereJsonContains('combination', [['option_id' => (int) $livewire->ownerRecord->productOptions->where('id', '!=', $optionValueModel->product_option_id)->first()->id]]);
                        })->get();

                    foreach ($productVariants as $productVariant) {
                        try {
                            if (count($productVariant->combination) == 1) {
                                $productVariant->delete();
                            }
                        } catch (Throwable $e) {
                            return $e->getMessage();
                        }
                    }

                    /**
                     * Sync product variants connected to this option value
                     */
                    $pairOptionValues = $pairOption->productOptionValues;

                    foreach ($pairOptionValues as $key => $pairOptionValue) {
                        try {
                            ProductVariant::create([
                                'product_id' => $livewire->ownerRecord->id,
                                'sku' => $livewire->ownerRecord->sku . $optionValueModel->id . $pairOptionValue->id,
                                'combination' => [
                                    [
                                        'option' => $optionValueModel->productOption->name,
                                        'option_id' => $optionValueModel->productOption->id,
                                        'option_value' => $optionValueModel->name,
                                        'option_value_id' => $optionValueModel->id,
                                    ],
                                    [
                                        'option' => $pairOption->name,
                                        'option_id' => $pairOption->id,
                                        'option_value' => $pairOptionValue->name,
                                        'option_value_id' => $pairOptionValue->id,
                                    ],
                                ],
                                'retail_price' => $livewire->ownerRecord->retail_price,
                                'selling_price' => $livewire->ownerRecord->selling_price,
                                'stock' => $livewire->ownerRecord->stock,
                                'status' => $livewire->ownerRecord->status,
                            ]);
                        } catch (Throwable $e) {
                            return $e->getMessage();
                        }
                    }
                } else {
                    ProductVariant::create([
                        'product_id' => $livewire->ownerRecord->id,
                        'sku' => $livewire->ownerRecord->sku . $optionValueModel->id,
                        'combination' => [
                            [
                                'option' => $optionValueModel->productOption->name,
                                'option_id' => $optionValueModel->productOption->id,
                                'option_value' => $optionValueModel->name,
                                'option_value_id' => $optionValueModel->id,
                            ],
                        ],
                        'retail_price' => $livewire->ownerRecord->retail_price,
                        'selling_price' => $livewire->ownerRecord->selling_price,
                        'stock' => $livewire->ownerRecord->stock,
                        'status' => $livewire->ownerRecord->status,
                    ]);
                }

                DB::commit();

                return $optionValueModel;
            } catch (Exception $e) {
                DB::rollBack();

                return 'Something went wrong';
            }
        });
    }

    protected static function getFormElements()
    {
        return [
            \Filament\Forms\Components\Select::make('product_option_id')
                ->label(trans('Product Option'))
                ->options(fn (HasRelationshipTable $livewire) => $livewire->ownerRecord->productOptions->pluck('name', 'id'))
                ->columnSpan(2)
                ->required()
                ->reactive(),
            \Filament\Forms\Components\TextInput::make('name')
                ->translateLabel()
                ->maxLength(100)
                ->columnSpan(2)
                ->required(),
            \Filament\Forms\Components\Group::make()
                ->schema([
                    \Filament\Forms\Components\Select::make('icon_type')
                        ->default('text')
                        ->required()
                        ->options(fn () => tenancy()->tenant?->features()->active(ColorPallete::class) ? [
                            'text' => 'Text',
                            'color_palette' => 'Color Palette',
                        ] : [
                            'text' => 'Text',
                        ])
                        ->columnSpan(
                            fn (Closure $get) => $get('icon_type') == 'color_palette' ? 1 : 2
                        )
                        ->hidden(function (Closure $get) {
                            if ($get('product_option_id')) {
                                $productOption = ProductOption::find((int) $get('product_option_id'));
                                \Log::info($productOption);
                                if ($productOption) {
                                    return $productOption->is_custom ? false : true;
                                }
                            }

                            return false;
                        })
                        ->reactive(),

                    \Filament\Forms\Components\ColorPicker::make('icon_value')
                        ->label(trans('Icon Value (HEX)'))
                        ->hidden(function (Closure $get) {
                            if (!($get('icon_type') === 'color_palette')) {
                                return true;
                            }

                            if ($get('product_option_id')) {
                                $productOption = ProductOption::find((int) $get('product_option_id'));

                                if ($productOption) {
                                    return $productOption->is_custom ? false : true;
                                }
                            }

                            return false;
                        }),
                ])
                ->columns(2)
                ->columnSpan(2),
        ];
    }
}
