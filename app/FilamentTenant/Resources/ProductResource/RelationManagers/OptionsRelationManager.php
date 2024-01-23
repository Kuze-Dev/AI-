<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ProductResource\RelationManagers;

use App\Features\ECommerce\ColorPallete;
use App\FilamentTenant\Support\CreateProductOptionValueAction;
use App\FilamentTenant\Support\EditProductOptionValueAction;
use App\FilamentTenant\Support\ManageProductOptionAction;
use Closure;
use Domain\Product\Models\ProductOption;
use Domain\Product\Models\ProductOptionValue;
use Domain\Product\Models\ProductVariant;
use Exception;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class OptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'productOptionValues';

    protected static ?string $recordTitleAttribute = 'Product Option Values';

    public Model $ownerRecord;

    public function form(Form $form): Form
    {
        return $form->schema([
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
                            fn (\Filament\Forms\Get $get) => $get('icon_type') == 'color_palette' ? 1 : 2
                        )
                        ->hidden(fn (\Filament\Forms\Get $get) => ! $get('option_is_custom'))
                        ->reactive(),

                    \Filament\Forms\Components\ColorPicker::make('icon_value')
                        ->label(trans('Icon Value (HEX)'))
                        ->hidden(fn (\Filament\Forms\Get $get) => ! ($get('icon_type') === 'color_palette' && $get('option_is_custom'))),
                ])
                ->columns(2)
                ->columnSpan(2),
        ]);
    }

    public function table(Table $table): Table
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
                Tables\Columns\TextColumn::make('icon_details')
                    ->translateLabel(),
            ])
            ->actions([
                EditProductOptionValueAction::proceed(),
                Tables\Actions\DeleteAction::make()
                    ->translateLabel()
                    ->action(function (ProductOptionValue $record, Tables\Actions\Action $action): void {
                        try {
                            if (! $record->productOption instanceof ProductOption) {
                                $action
                                    ->failureNotificationTitle(trans('The option value is unlinked from an option.'))
                                    ->failure();

                                return;
                            }

                            ProductVariant::where('product_id', $record->productOption->product_id)
                                ->where(function (Builder $query) use ($record) {
                                    $query->whereJsonContains('combination', [['option_value_id' => $record->id]]);
                                })->delete();

                            $record->delete();

                            $action
                                ->successNotificationTitle(trans('Option value has been removed.'))
                                ->success();
                        } catch (Exception) {
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
                                if (! isset($record->productOption)) {
                                    return;
                                }

                                ProductVariant::where('product_id', $record->productOption->product_id)
                                    ->where(function (Builder $query) use ($record) {
                                        $query->whereJsonContains('combination', [['option_value_id' => $record->id ?? 0]]);
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
            ->headerActions([
                ManageProductOptionAction::proceed(),
                CreateProductOptionValueAction::proceed(),
            ])
            ->defaultSort('id', 'asc');
    }
}
