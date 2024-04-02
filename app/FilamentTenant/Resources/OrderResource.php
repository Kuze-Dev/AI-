<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Features\ECommerce\AllowGuestOrder;
use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources\OrderResource\Schema;
use Domain\Order\Enums\OrderStatuses;
use Domain\Order\Enums\OrderUserType;
use Domain\Order\Models\Order;
use Domain\Tenant\TenantFeatureSupport;
use Filament\Forms;
use Filament\Infolists;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $recordTitleAttribute = 'reference';

    #[\Override]
    public static function getNavigationGroup(): ?string
    {
        return trans('eCommerce');
    }

    #[\Override]
    public static function getNavigationBadge(): ?string
    {
        return (string) Order::whereIn('status', [OrderStatuses::PENDING, OrderStatuses::FORPAYMENT])->count();
    }

    #[\Override]
    public static function infolist(Infolists\Infolist $infolist): Infolists\Infolist
    {
        return $infolist
            ->schema([

                Infolists\Components\Group::make()
                    ->schema([

                        Infolists\Components\Section::make()
                            ->heading(function (Order $record) {
                                if (TenantFeatureSupport::active(AllowGuestOrder::class)) {
                                    return trans('Customer');
                                }

                                return $record->customer_id
                                    ? trans('Customer (Registered)')
                                    : trans('Customer (Guest)');
                            })
                            ->collapsible()
                            ->schema([

                                Infolists\Components\TextEntry::make('customer_first_name')
                                    ->label(trans('First Name'))
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large),

                                Infolists\Components\TextEntry::make('customer_last_name')
                                    ->label(trans('Last Name'))
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large),

                                Infolists\Components\TextEntry::make('customer_email')
                                    ->label(trans('Email'))
                                    ->icon('heroicon-m-envelope')
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large),

                                Infolists\Components\TextEntry::make('customer_mobile')
                                    ->label(trans('Contact Number'))
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large),

                            ])
                            ->columns(),

                        Infolists\Components\Section::make()
                            ->heading(trans('Shipping Address'))
                            ->collapsible()
                            ->schema([

                                Infolists\Components\TextEntry::make('shippingAddress.address_line_1')
                                    ->label(trans('House/Unit/Flr #, Bldg Name, Blk or Lot #'))
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                    ->columnSpanFull(),

                                Infolists\Components\TextEntry::make('shippingAddress.country')
                                    ->label(trans('Country'))
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large),

                                Infolists\Components\TextEntry::make('shippingAddress.state')
                                    ->label(trans('State'))
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large),

                                Infolists\Components\TextEntry::make('shippingAddress.city')
                                    ->label(trans('City/Province'))
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large),

                                Infolists\Components\TextEntry::make('shippingAddress.zip_code')
                                    ->label(trans('Zip Code'))
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large),

                            ])
                            ->columns(),

                        Infolists\Components\Section::make()
                            ->heading(trans('Billing Address'))
                            ->collapsible()
                            ->schema([

                                Infolists\Components\TextEntry::make('billingAddress.address_line_1')
                                    ->label(trans('House/Unit/Flr #, Bldg Name, Blk or Lot #'))
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                    ->columnSpanFull(),

                                Infolists\Components\TextEntry::make('billingAddress.country')
                                    ->label(trans('Country'))
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large),

                                Infolists\Components\TextEntry::make('billingAddress.state')
                                    ->label(trans('State'))
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large),

                                Infolists\Components\TextEntry::make('billingAddress.city')
                                    ->label(trans('City/Province'))
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large),

                                Infolists\Components\TextEntry::make('billingAddress.zip_code')
                                    ->label(trans('Zip Code'))
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                            ])
                            ->columns(),

                        Infolists\Components\Section::make()
                            ->heading(trans('Others'))
                            ->collapsible()
                            ->schema([

                                Infolists\Components\TextEntry::make('payments.paymentMethod.title')
                                    ->label(trans('Payment Method'))
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large),

                                Infolists\Components\TextEntry::make('shippingMethod.title')
                                    ->label(trans('Shipping Method'))
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                            ])
                            ->columns(),
                    ])
                    ->columnSpan(2),

                Infolists\Components\Group::make()
                    ->schema(Schema::summarySchema())
                    ->columnSpan(1),

            ])->columns(3);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->label(trans('Order ID'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer_id')
                    ->label(trans('Customer Type'))
                    ->visible(fn () => TenantFeatureSupport::active(AllowGuestOrder::class))
                    ->alignLeft()
                    ->default(false)
                    ->formatStateUsing(
                        fn (?string $state) => filled($state)
                            ? trans('Registered')
                            : trans('Guest')
                    ),
                Tables\Columns\TextColumn::make('customer_full_name')
                    ->label(trans('Customer'))
                    ->sortable([
                        'customer_first_name',
                        'customer_last_name',
                    ])
                    ->searchable([
                        'customer_first_name',
                        'customer_last_name',
                    ])
                    ->wrap(),
                Tables\Columns\TextColumn::make('tax_total')
                    ->alignRight()
                    ->label(trans('Tax Total'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn (Order $record) => $record->currency_symbol.' '.number_format((float) $record->tax_total, 2, '.', ',')),
                Tables\Columns\TextColumn::make('total')
                    ->formatStateUsing(fn (Order $record) => $record->currency_symbol.' '.number_format((float) $record->total, 2, '.', ','))
                    ->alignRight()
                    ->label(trans('Total'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('payments.paymentMethod.title')
                    ->translateLabel()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('shippingMethod.title')
                    ->translateLabel()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_paid')
                    ->label(trans('Paid'))
                    ->alignRight()
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->sortable()
                    ->label(trans('Order Date'))
                    ->dateTime(),
                Tables\Columns\TextColumn::make('status')
                    ->translateLabel()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label(trans('Created from')),
                        Forms\Components\DatePicker::make('created_until')
                            ->label(trans('Created until')),
                    ])
                    ->query(
                        fn (Builder $query, array $data): Builder => $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            )
                    )
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = 'Created from '.Carbon::parse($data['created_from'])->toFormattedDateString();
                        }

                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Created until '.Carbon::parse($data['created_until'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
                Tables\Filters\SelectFilter::make('status')->label(trans('Status'))
                    ->options([
                        OrderStatuses::PENDING->value => trans('Pending'),
                        OrderStatuses::FORPAYMENT->value => trans('For Payment'),
                        OrderStatuses::FORAPPROVAL->value => trans('For Approval'),
                        OrderStatuses::PROCESSING->value => trans('Processing'),
                        OrderStatuses::CANCELLED->value => trans('Cancelled'),
                        OrderStatuses::REFUNDED->value => trans('Refunded'),
                        OrderStatuses::PACKED->value => trans('Packed'),
                        OrderStatuses::SHIPPED->value => trans('Shipped'),
                        OrderStatuses::DELIVERED->value => trans('Delivered'),
                        OrderStatuses::FULFILLED->value => trans('Fulfilled'),
                    ])
                    ->attribute('status'),
                Tables\Filters\SelectFilter::make('customer_id')->label(trans('Customer Type'))
                    ->hidden(fn () => TenantFeatureSupport::inactive(AllowGuestOrder::class))
                    ->options([
                        OrderUserType::REGISTERED->value => trans('Registered'),
                        OrderUserType::GUEST->value => trans('Guest'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        $query->when(filled($data['value']), function (Builder $query) use ($data) {
                            if ($data['value'] === OrderUserType::REGISTERED->value) {
                                $query->whereNotNull('customer_id');

                                return;
                            }
                            $query->whereNull('customer_id');
                        });
                    }),
            ])
            ->bulkActions([
                // Tables\Actions\BulkAction::make('export')
                //     ->action(function (Collection $records) {
                //         return Excel::download(new ExportCollection($records), 'orders.csv');
                //     })
                //     ->color('primary')
                //     ->icon('heroicon-o-check')
            ])
            ->actions([])
            ->defaultSort('created_at', 'desc');
    }

    #[\Override]
    public static function canCreate(): bool
    {
        return false;
    }

    #[\Override]
    public static function getRelations(): array
    {
        return [
            OrderResource\RelationManagers\OrderLinesRelationManager::class,
            ActivitiesRelationManager::class,
        ];
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => OrderResource\Pages\ListOrders::route('/'),
            'view' => OrderResource\Pages\ViewOrder::route('/{record}'),
            'details' => OrderResource\Pages\ViewOrderDetails::route('/{record}/details'),
        ];
    }
}
