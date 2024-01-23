<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\OrderResource\RelationManagers;

use App\FilamentTenant\Resources\OrderResource;
use Domain\Order\Models\OrderLine;
use Domain\Product\Models\ProductVariant;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;

class OrderLinesRelationManager extends RelationManager
{
    protected static string $relationship = 'orderLines';

    protected static ?string $recordTitleAttribute = 'label';

    public function table(Table $table): Table
    {

        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')
                    ->collection('order_line_images')
                    ->default(
                        fn (OrderLine $record) => $record->getFirstMediaUrl('order_line_images') == null
                            ? 'https://via.placeholder.com/500x300/333333/fff?text=No+preview+available'
                            : null
                    )->square(),
                Tables\Columns\TextColumn::make('name')
                    ->label(trans('Product Name'))
                    ->formatStateUsing(fn (string $state) => Str::limit($state, 30))
                    ->description(function (OrderLine $record) {
                        if ($record->purchasable_type == ProductVariant::class) {
                            /** @var \Domain\Product\Models\ProductVariant $productVariant */
                            $productVariant = $record->purchasable_data;

                            $combinations = array_values($productVariant['combination']);
                            $optionValues = array_column($combinations, 'option_value');
                            $variantString = implode(' / ', array_map('ucfirst', $optionValues));

                            return Str::limit($variantString, 30);
                        }

                        return '';
                    })
                    ->alignLeft(),
                Tables\Columns\TextColumn::make('unit_price')
                    ->formatStateUsing(function (self $livewire, OrderLine $record) {
                        /** @var \Domain\Order\Models\Order $order */
                        $order = $livewire->getOwnerRecord();

                        return $order->currency_symbol.' '.number_format($record->unit_price, 2, '.', '');
                    })
                    ->label(trans('Unit Price')),
                Tables\Columns\TextColumn::make('quantity')->label(trans('Quantity')),
                Tables\Columns\TextColumn::make('sub_total')
                    ->formatStateUsing(function (self $livewire, OrderLine $record) {
                        /** @var \Domain\Order\Models\Order $order */
                        $order = $livewire->getOwnerRecord();

                        return $order->currency_symbol.' '.number_format($record->sub_total, 2, '.', '');
                    })
                    ->label(trans('Amount')),
            ])
            ->headerActions([
                Tables\Actions\Action::make('view')
                    ->label(trans('View Details'))
                    ->color('gray')
                    ->action(function ($livewire) {
                        return redirect(OrderResource::getUrl('details', ['record' => $livewire->ownerRecord]));
                    })->button(),
            ]);
    }

    protected function getTableContentFooter(): ?View
    {

        return view('filament.tables.order.order-lines-footer');
    }
}
