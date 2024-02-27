<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\OrderResource\Pages;

use App\Filament\Support\Infolists\SpatieMediaLibraryImageEntry;
use App\FilamentTenant\Resources\OrderResource;
use App\FilamentTenant\Resources\OrderResource\Schema;
use Domain\Order\Models\Order;
use Domain\Order\Models\OrderLine;
use Domain\Product\Models\ProductVariant;
use Filament\Infolists;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

/**
 * @property-read Order $record
 */
class ViewOrderDetails extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    public function getHeading(): string|Htmlable
    {
        return trans('Order Details #:order', ['order' => $this->record->reference]);
    }

    public function getBreadcrumb(): string
    {
        return trans('View details');
    }

    public function getRelationManagers(): array
    {
        return [];
    }

    public function infolist(Infolists\Infolist $infolist): Infolists\Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make()
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('orderLines')
                            ->hiddenLabel()
                            ->schema([

                                Infolists\Components\SpatieMediaLibraryImageEntry::make('order_line_images')
                                    ->hiddenLabel()
                                    ->collection('order_line_images')
                                    ->columnSpanFull(),

                                Infolists\Components\TextEntry::make('name')
                                    ->label(trans('Product'))
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large),

                                Infolists\Components\TextEntry::make('purchasable_type')
                                    ->label(trans('Variant'))
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                    ->formatStateUsing(function (OrderLine $record) {
                                        if ($record->purchasable_type == ProductVariant::class) {
                                            $combinations = array_values($record->purchasable_data['combination']);
                                            $optionValues = array_column($combinations, 'option_value');

                                            return implode(' / ', array_map('ucfirst', $optionValues));
                                        }

                                        return 'N/A';
                                    }),

                                Infolists\Components\TextEntry::make('quantity')
                                    ->translateLabel()
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large),

                                Infolists\Components\TextEntry::make('sub_total')
                                    ->label(trans('Amount'))
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                    ->prefix(fn (OrderLine $record) => $record->order->currency_symbol),

                                Infolists\Components\Actions::make([])
                                    ->fullWidth()
                                    ->alignCenter()
                                    ->columnSpanFull()
                                    ->hidden(
                                        fn (OrderLine $record) => blank($record->remarks_data) &&
                                            $record->getFirstMedia('order_line_notes') === null
                                    )
                                    ->actions([
                                        Infolists\Components\Actions\Action::make('view_remarks')
                                            ->translateLabel()
                                            ->button()
                                            ->outlined()
                                            ->icon('heroicon-o-eye')
                                            ->slideOver()
                                            ->modalHeading(trans('Customer Remarks'))
                                            ->size('sm')
                                            ->disabledForm()
                                            ->modalSubmitAction(false)
                                            ->modalCancelAction(false)
                                            ->infolist(fn (OrderLine $record) => [

                                                Infolists\Components\TextEntry::make('remarks_data')
                                                    ->label(trans('Remarks'))
                                                    ->state(fn () => $record->remarks_data['notes'] ?? ''),

                                                SpatieMediaLibraryImageEntry::make('order_line_notes')
                                                    ->label(trans('Customer Upload'))
                                                    ->model(fn () => $record)
                                                    ->collection('order_line_notes'),
                                            ]),
                                    ]),
                            ])
                            ->columns(),
                    ])
                    ->columnSpan(2),

                Infolists\Components\Group::make()
                    ->schema(Schema::summarySchema())
                    ->columnSpan(1),

            ])->columns(3);
    }
}
