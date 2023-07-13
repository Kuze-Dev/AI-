<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\OrderResource\RelationManagers;

use App\FilamentTenant\Resources\OrderResource;
use Domain\Order\Models\Order;
use Domain\Order\Models\OrderLine;
use Domain\Product\Models\ProductVariant;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;

class OrderLinesRelationManager extends RelationManager
{
    protected static string $relationship = 'orderLines';

    protected static ?string $recordTitleAttribute = 'label';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')
                    ->collection('order_line_images')
                    ->default(
                        fn (OrderLine $record) => $record->getFirstMediaUrl('order_line_images') === null
                            ? 'https://via.placeholder.com/500x300/333333/fff?text=No+preview+available'
                            : null
                    )->square(),
                Tables\Columns\TextColumn::make('name')->label('Product Name')
                    ->description(function (OrderLine $record) {
                        if ($record->purchasable_type == ProductVariant::class) {
                            $variant = array_values($record->purchasable_data['combination']);
                            $variantString = implode(' / ', array_map('ucfirst', $variant));
                            return $variantString;
                        }
                        return "";
                    })
                    ->alignLeft(),
                Tables\Columns\TextColumn::make('unit_price')->label('Unit Price'),
                Tables\Columns\TextColumn::make('quantity')->label('Quantity'),
                Tables\Columns\TextColumn::make('sub_total')->label('Amount'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('view')->label("View Details")->color("secondary")->action(function ($livewire) {
                    return redirect(OrderResource::getUrl('details', ['record' => $livewire->ownerRecord]));
                })->button()
            ]);
    }

    protected function getTableContentFooter(): ?View
    {
        return view('filament.tables.order.order-lines-footer');;
    }
}
