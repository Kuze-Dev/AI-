<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ServiceOrderResource\RelationManagers;

use Domain\ServiceOrder\Models\ServiceBill;
use Domain\Taxation\Enums\PriceDisplay;
use Filament\Infolists;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Number;

/**
 * @property-read \Domain\ServiceOrder\Models\ServiceOrder $ownerRecord
 */
class ServiceBillsRelationManager extends RelationManager
{
    protected static string $relationship = 'serviceBills';

    protected static ?string $title = 'Service Bills';

    public function infolist(Infolists\Infolist $infolist): Infolists\Infolist
    {
        return $infolist->schema([
            Infolists\Components\Group::make()
                ->columnSpan(2)
                ->schema([

                    Infolists\Components\Section::make(trans('Service'))
                        ->columns()
                        ->schema([

                            Infolists\Components\TextEntry::make('service')
                                ->state(fn () => $this->ownerRecord->service_name),

                            Infolists\Components\TextEntry::make('service Price')
                                ->state(fn () => Number::currency($this->ownerRecord->service_price, $this->ownerRecord->currency_code)),

                        ]),

                    Infolists\Components\Section::make(trans('Additional Charges'))
                        ->visible(fn (ServiceBill $record) => filled($record->additional_charges))
                        ->schema([

                            Infolists\Components\RepeatableEntry::make('additional_charges')
                                ->columns(3)
                                ->hiddenLabel()
                                ->schema([
                                    Infolists\Components\TextEntry::make('name')
                                        ->translateLabel(),

                                    Infolists\Components\TextEntry::make('quantity')
                                        ->translateLabel(),

                                    Infolists\Components\TextEntry::make('price')
                                        ->translateLabel(),
                                ]),

                        ]),

                ]),

            Infolists\Components\Group::make()
                ->columnSpan(1)
                ->schema([

                    Infolists\Components\Section::make(trans('Summary'))
                        ->schema([

                            Infolists\Components\TextEntry::make('status')
                                ->translateLabel()
                                ->inlineLabel()
                                ->badge(),

                            //                        self::summaryProofOfPaymentButton(),

                            Infolists\Components\TextEntry::make('service_price')
                                ->translateLabel()
                                ->inlineLabel()
                                ->money($this->ownerRecord->currency_code),

                            Infolists\Components\TextEntry::make('additional_charges')
                                ->translateLabel()
                                ->inlineLabel()
                                ->state(function (ServiceBill $record) {
                                    $sum = 0;

                                    foreach ($record->additional_charges as $charge) {

                                        if (isset($charge['price']) && isset($charge['quantity'])) {
                                            $sum += $charge['price'] * $charge['quantity'];
                                        }

                                    }

                                    return $sum;
                                })
                                ->money($this->ownerRecord->currency_code),

                            Infolists\Components\TextEntry::make('tax_percentage')
                                ->label(fn (ServiceBill $record) => trans('Tax (:tax_percentage%)', ['tax_percentage' => $record->tax_percentage]))
                                ->inlineLabel()
                                ->visible(
                                    fn (ServiceBill $record) => $record->tax_display !== null
                                )
                                ->state(function (ServiceBill $record) {
                                    if ($record->tax_display == PriceDisplay::INCLUSIVE->value) {
                                        return 'Inclusive';
                                    }

                                    return Number::currency($record->tax_total, $this->ownerRecord->currency_code);
                                }),

                            Infolists\Components\TextEntry::make('total_amount')
                                ->label(trans('Total Price'))
                                ->inlineLabel()
                                ->money($this->ownerRecord->currency_code),

                        ]),

                ]),
        ])
            ->columns(3);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->translateLabel()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label(trans('Amount'))
                    ->sortable()
                    ->money(currency: fn () => $this->ownerRecord->currency_code),

                Tables\Columns\TextColumn::make('total_balance')
                    ->label(trans('Balance'))
                    ->sortable()
                    ->money(currency: fn () => $this->ownerRecord->currency_code),

                Tables\Columns\TextColumn::make('status')
                    ->translateLabel()
                    ->badge()
                    ->inline(),

                Tables\Columns\TextColumn::make('bill_date')
                    ->translateLabel()
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('due_date')
                    ->label(trans('Due at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label(trans('View Details')),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
