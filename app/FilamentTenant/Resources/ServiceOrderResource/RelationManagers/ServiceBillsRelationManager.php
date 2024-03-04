<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ServiceOrderResource\RelationManagers;

use App\FilamentTenant\Support\BadgeLabel;
use App\FilamentTenant\Support\TextLabel;
use Domain\ServiceOrder\Enums\ServiceBillStatus;
use Domain\ServiceOrder\Models\ServiceBill;
use Domain\Taxation\Enums\PriceDisplay;
use Filament\Infolists;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

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
                        ->schema([
                            Infolists\Components\Grid::make(2)->schema([

                                Infolists\Components\TextEntry::make('service')
                                    ->formatStateUsing(fn () => $this->ownerRecord->service_name),

                                Infolists\Components\TextEntry::make('service Price')
                                    ->formatStateUsing(fn ($record) => $this->ownerRecord->currency_symbol.' '.number_format($this->ownerRecord->service_price, 2, '.', ',')),
                            ]),

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

                            Infolists\Components\Group::make()
                                ->columns(2)
                                ->schema([
                                    //                                TextLabel::make('')
                                    //                                    ->label(trans('Status'))
                                    //                                    ->alignLeft()
                                    //                                    ->size('md')
                                    //                                    ->inline()
                                    //                                    ->readOnly(),
                                    //                                BadgeLabel::make(trans('status'))->formatStateUsing(function (string $state): string {
                                    //                                    $newState = str_replace('_', ' ', strtolower($state));
                                    //
                                    //                                    return ucfirst($newState);
                                    //                                })
                                    //                                    ->color(function ($state) {
                                    //                                        $newState = str_replace(' ', '_', strtolower($state));
                                    //
                                    //                                        return match ($newState) {
                                    //                                            ServiceBillStatus::PENDING->value => 'warning',
                                    //                                            ServiceBillStatus::PAID->value => 'success',
                                    //                                            default => 'secondary',
                                    //                                        };
                                    //                                    })->inline()
                                    //                                    ->alignRight(),
                                ]),

                            //                        self::summaryProofOfPaymentButton(),

                            Infolists\Components\Group::make()
                                ->columns(2)
                                ->schema([
                                    //                            TextLabel::make('')
                                    //                                ->label(trans('Service Price'))
                                    //                                ->alignLeft()
                                    //                                ->size('md')
                                    //                                ->inline()
                                    //                                ->readOnly(),
                                    //                            TextLabel::make('')
                                    //                                ->label(fn ($record) => $record->serviceOrder->currency_symbol.' '.number_format($record->service_price, 2, '.', ','))
                                    //                                ->alignRight()
                                    //                                ->size('md')
                                    //                                ->inline()
                                    //                                ->readOnly(),
                                    //                            TextLabel::make('')
                                    //                                ->label(trans('Additional Charges'))
                                    //                                ->alignLeft()
                                    //                                ->size('md')
                                    //                                ->inline()
                                    //                                ->readOnly(),
                                    //                            TextLabel::make('')
                                    //                                ->label(fn ($record, \Filament\Forms\Get $get) => $record->serviceOrder->currency_symbol.' '.number_format(array_reduce($get('additional_charges'), function ($carry, $data) {
                                    //                                        if (isset($data['price']) && is_numeric($data['price']) && isset($data['quantity']) && is_numeric($data['quantity'])) {
                                    //                                            return $carry + ($data['price'] * $data['quantity']);
                                    //                                        }
                                    //
                                    //                                        return $carry;
                                    //                                    }, 0), 2, '.', ','))
                                    //                                ->alignRight()
                                    //                                ->size('md')
                                    //                                ->inline()
                                    //                                ->readOnly(),
                                    Infolists\Components\Group::make()
                                        ->columns(2)
                                        ->columnSpan(2)
                                        ->schema([
                                            //                                        TextLabel::make('')
                                            //                                            ->label(fn ($record) => trans('Tax (').$record->tax_percentage.'%)')
                                            //                                            ->alignLeft()
                                            //                                            ->size('md')
                                            //                                            ->inline()
                                            //                                            ->readOnly(),
                                            //                                        TextLabel::make('')
                                            //                                            ->label(fn (ServiceBill $record, \Filament\Forms\Get $get) => $record->tax_display == PriceDisplay::INCLUSIVE->value ? 'Inclusive'
                                            //                                                :
                                            //                                                $record->serviceOrder?->currency_symbol.' '.number_format($record->tax_total, 2, '.', '.'))
                                            //                                            ->alignRight()
                                            //                                            ->size('md')
                                            //                                            ->inline()
                                            //                                            ->readOnly(),
                                        ])
                                        ->visible(
                                            fn (ServiceBill $record) => $record->tax_display !== null
                                        ),
                                    //                                    TextLabel::make('')
                                    //                                        ->label(trans('Total Price'))
                                    //                                        ->alignLeft()
                                    //                                        ->size('md')
                                    //                                        ->inline()
                                    //                                        ->readOnly()
                                    //                                        ->color('primary'),
                                    //                                    TextLabel::make('')
                                    //                                        ->label(fn (ServiceBill $record, \Filament\Forms\Get $get) => $record->serviceOrder?->currency_symbol.' '.number_format($record->total_amount, 2, '.', '.'))
                                    //                                        ->alignRight()
                                    //                                        ->size('md')
                                    //                                        ->inline()
                                    //                                        ->readOnly()
                                    //                                        ->color('primary'),
                                ]),

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
