<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ProductResource\RelationManagers;

use App\Features\ECommerce\ColorPallete;
use DB;
use Domain\Product\Models\ProductOptionValue;
use Domain\Product\Models\ProductVariant;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class OptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'productOptionValues';

    protected static ?string $recordTitleAttribute = 'Product Option Values';

    public static function form(Form $form): Form
    {
        // Temporarily commented
        return $form->schema([
            \Filament\Forms\Components\TextInput::make('name')
                ->translateLabel()
                ->maxLength(100)
                ->lazy()
                ->columnSpan(2)
                // ->columnSpan(
                //     fn (Closure $get) => $get('../../is_custom') ? 1 : 2
                // )
                ->required(),
            // \Filament\Forms\Components\Select::make('icon_type')
            //     ->default('text')
            //     ->required()
            //     ->options(fn () => tenancy()->tenant?->features()->active(ColorPallete::class) ? [
            //         'text' => 'Text',
            //         'color_palette' => 'Color Palette',
            //     ] : [
            //         'text' => 'Text',
            //     ]),

            // \Filament\Forms\Components\ColorPicker::make('icon_value')
            //     ->label(trans('Icon Value (HEX)')),
            // ->hidden(fn (Closure $get) => !($get('icon_type') === 'color_palette' && $get('../../is_custom'))),
            // ->hidden(fn (Closure $get) => !$get('../../is_custom'))
            // ->reactive(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product_option_name')
                    ->label(trans('Option Name'))
                    ->searchable(query: function (Builder $query, string $search) {
                        return $query->whereHas('productOption', function ($query) use ($search) {
                            $query->where('product_options.name', 'like', "%{$search}%");
                        })->get();
                    }),
                Tables\Columns\TextColumn::make('name')
                    ->label(trans('Option Value'))
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query
                            ->where('product_option_values.name', 'like', "%{$search}%");
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('product_option_values.name', $direction);
                    }),
                Tables\Columns\TextColumn::make('iconDetails')
                    ->translateLabel(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->translateLabel()
                    ->action(function (ProductOptionValue $record, Tables\Actions\Action $action): void {
                        try {
                            ProductVariant::where('product_id', $record->productOption->product_id)
                                ->where(function (Builder $query) use ($record) {
                                    $query->whereJsonContains('combination', [['option_value_id' => $record->id]]);
                                })->delete();

                            $record->delete();

                            $action
                                ->successNotificationTitle(trans('Option value has been removed.'))
                                ->success();
                        } catch (Exception $e) {
                            $action->failureNotificationTitle(trans('Failed to remove Option value.'))
                                ->failure();
                        }
                    })
                    ->authorize('delete'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->translateLabel()
                    ->action(function (Collection $records): void {
                        foreach ($records as $record) {
                            try {
                                ProductVariant::where('product_id', $record->productOption->product_id)
                                    ->where(function (Builder $query) use ($record) {
                                        $query->whereJsonContains('combination', [['option_value_id' => $record->id]]);
                                    })->delete();

                                $record->delete();
                            } catch (Exception $e) {
                                Log::error([
                                    'message' => $e->getMessage(),
                                    'code' => $e->getCode(),
                                    'file' => $e->getFile(),
                                    'line' => $e->getLine(),
                                ]);

                                throw $e;
                            }
                        }
                    }),
            ])
            ->defaultSort('id', 'asc');
    }
}
