<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support;

use Domain\Product\Models\ProductOption;
use Domain\Product\Models\ProductOptionValue;
use Domain\Product\Models\ProductVariant;
use Exception;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

class EditProductOptionValueAction
{
    public static function proceed(): Tables\Actions\EditAction
    {
        return Tables\Actions\EditAction::make()
            ->translateLabel()
            ->mutateRecordDataUsing(function (array $data): array {
                $productOption = ProductOption::find($data['product_option_id']);

                if ($productOption instanceof ProductOption) {
                    $data['option_is_custom'] = $productOption->is_custom;
                    $data['icon_type'] = $data['data']['icon_type'];
                    $data['icon_value'] = $data['data']['icon_value'];
                }

                return $data;
            })
            ->using(
                fn (ProductOptionValue $record, array $data, Tables\Actions\Action $action): Model|string => self::processUpdate($record, $data, $action)
            )->authorize('update');
    }

    protected static function processUpdate(ProductOptionValue $record, array $data, Tables\Actions\Action $action): Model|string
    {
        return DB::transaction(function () use ($record, $data, $action) {
            try {
                DB::beginTransaction();

                // Check if option value has duplicate
                $productOptionValues = ProductOptionValue::select('id')
                    ->whereProductOptionId($record->option_id ?? 0)
                    ->whereName($data['name'])->get();

                if (count($productOptionValues->toArray()) > 1) {
                    $action->failureNotificationTitle(trans('Option value name has duplicate.'))
                        ->failure();

                    $action->halt();

                    return 'halted';
                }

                $record->update([
                    'name' => $data['name'],
                    'data' => ['icon_type' => $data['icon_type'] ?? 'text', 'icon_value' => $data['icon_value'] ?? ''],
                ]);

                if (! $record->productOption instanceof ProductOption) {
                    $action->failureNotificationTitle(trans('The option value is unlinked from an option.'))
                        ->failure();

                    $action->halt();

                    return 'halted';
                }

                // Sync product variants connected to this option value
                $productVariants = ProductVariant::where('product_id', $record->productOption->product_id)
                    ->where(function (Builder $query) use ($record) {
                        $query->whereJsonContains('combination', [['option_value_id' => $record->id]]);
                    })->get();

                foreach ($productVariants as $productVariant) {
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
                        return $e->getMessage();
                    }
                }

                // Add missing variant combination
                DB::commit();

                return $record;
            } catch (Exception) {
                DB::rollBack();

                return 'Something went wrong';
            }
        });
    }
}
