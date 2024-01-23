<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support;

use Closure;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductOption;
use Domain\Product\Models\ProductVariant;
use Exception;
use Filament\Tables;
use Filament\Tables\Contracts\HasRelationshipTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

class ManageProductOptionAction
{
    public static function proceed(): Tables\Actions\CreateAction
    {
        return Tables\Actions\CreateAction::make('productOptions')
            ->label('Manage Product Option')
            ->modalHeading(trans('Manage Product Option'))
            ->modalButton('Save')
            ->successNotificationTitle('Product option managed.')
            ->disableCreateAnother(true)
            ->form(
                fn () => self::getFormElements()
            )
            ->using(function (HasRelationshipTable $livewire, array $data, Tables\Actions\Action $action): Model|string {
                return self::processProductOption($livewire, $data, $action);
            });
    }

    protected static function processProductOption(HasRelationshipTable $livewire, array $data, Tables\Actions\Action $action): Model|string
    {
        return DB::transaction(function () use ($livewire, $data, $action) {
            try {
                DB::beginTransaction();

                foreach ($data['options'] as $option) {
                    try {
                        $isThereNameAdjustment = false;

                        if (! isset($livewire->ownerRecord)) {
                            $action->failureNotificationTitle(trans('No owner record set.'))
                                ->failure();

                            $action->halt();

                            return 'halted';
                        }

                        /** @var Product $livewireOwnerRecord */
                        $livewireOwnerRecord = $livewire->ownerRecord;

                        $productOption = ProductOption::whereProductId($livewireOwnerRecord->id);

                        if (isset($option['id'])) {
                            $foundOption = $productOption->whereId($option['id'])->first();
                        } else {
                            $foundOption = $productOption->whereName($option['name'])->first();
                        }

                        if (! $foundOption instanceof ProductOption) {
                            $foundOption = ProductOption::create([
                                'product_id' => $livewireOwnerRecord->id,
                                'name' => $option['name'],
                                'is_custom' => $option['is_custom'],
                            ]);
                        } else {
                            if ($foundOption->name != $option['name']) {
                                $foundOption->name = $option['name'];
                                $isThereNameAdjustment = true;
                            }

                            $foundOption->is_custom = $option['is_custom'];
                            $foundOption->save();
                        }

                        // If may existing variants, detect and update them.
                        if ($isThereNameAdjustment) {
                            $productVariants = ProductVariant::where('product_id', $foundOption->product_id)
                                ->where(function (Builder $query) use ($foundOption) {
                                    $query->whereJsonContains('combination', [['option_id' => $foundOption->id]]);
                                })->get();

                            foreach ($productVariants as $productVariant) {
                                try {
                                    $combinations = [];
                                    foreach ($productVariant->combination as $key => $item) {
                                        if ($item['option_id'] === $foundOption->id) {
                                            $item['option'] = $foundOption->name;
                                        }
                                        $combinations[$key] = $item;
                                    }

                                    $productVariant->combination = $combinations;
                                    $productVariant->save();
                                } catch (Throwable $e) {
                                    return $e->getMessage();
                                }
                            }
                        }
                    } catch (Throwable $e) {
                        return $e->getMessage();
                    }
                }

                DB::commit();

                return $foundOption ?? 'Process finished.';
            } catch (Exception) {
                DB::rollBack();

                return 'Something went wrong';
            }
        });
    }

    protected static function getFormElements(): array
    {
        return [
            \Filament\Forms\Components\Repeater::make('options')
                ->translateLabel()
                ->reactive()
                ->disableItemDeletion()
                ->afterStateHydrated(function (\Filament\Forms\Components\Repeater $component, ?Product $record, ?array $state, HasRelationshipTable $livewire) {
                    /** @phpstan-ignore-next-line */
                    $productOptions = $livewire->ownerRecord->productOptions->toArray();
                    if (($productOptions) !== null) {
                        $component->state($productOptions);
                    }
                })
                ->itemLabel(fn (array $state): ?string => $state['name'])
                ->schema([
                    \Filament\Forms\Components\Group::make()
                        ->schema([
                            \Filament\Forms\Components\TextInput::make('name')
                                ->translateLabel()
                                ->maxLength(100)
                                ->columnSpan(
                                    function (\Filament\Forms\Get $get) {
                                        return ! is_null($get('id'))
                                            ? ($get('../*')[0]['id'] !== $get('id') ? 2 : 1)
                                            : (count($get('../*')) === 1 ? 1 : 2);
                                    }
                                )
                                ->required(),
                            \Filament\Forms\Components\Toggle::make('is_custom')
                                ->label(
                                    fn ($state) => $state ? ucfirst(trans('Custom')) : ucfirst(trans('Regular'))
                                )
                                ->hidden(
                                    function (\Filament\Forms\Get $get) {
                                        return ! is_null($get('id'))
                                            ? ($get('../*')[0]['id'] !== $get('id'))
                                            : (count($get('../*')) === 1 ? false : true);
                                    }
                                )
                                ->extraAttributes(['class' => 'mt-2 mb-1'])
                                ->default(false)
                                ->helperText('Identify whether the option value in the form has customization.')
                                ->reactive(),
                        ])
                        ->columns(2)
                        ->columnSpan(2),
                ])
                ->disableItemMovement()
                ->maxItems(2)
                ->collapsible(),
        ];
    }
}
