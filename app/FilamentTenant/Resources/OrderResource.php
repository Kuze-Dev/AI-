<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Features\ECommerce\AllowGuestOrder;
use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\Settings\OrderSettings;
use Domain\Order\Enums\OrderStatuses;
use Domain\Order\Enums\OrderUserType;
use Domain\Order\Events\AdminOrderBankPaymentEvent;
use Domain\Order\Events\AdminOrderStatusUpdatedEvent;
use Domain\Order\Models\Order;
use Domain\Payments\Models\Payment;
use Domain\Tenant\TenantHelpers;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists\Components\Actions;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\TextEntry\TextEntrySize;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $recordTitleAttribute = 'reference';

    public static function getNavigationGroup(): ?string
    {
        return trans('eCommerce');
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) Order::whereIn('status', [OrderStatuses::PENDING, OrderStatuses::FORPAYMENT])->count();
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([

                Group::make()
                    ->schema([

                        Section::make()
                            ->heading(function (Order $record) {
                                if (TenantHelpers::isFeatureActive(AllowGuestOrder::class)) {
                                    return trans('Customer');
                                }

                                return $record->customer_id
                                    ? trans('Customer (Registered)')
                                    : trans('Customer (Guest)');
                            })
                            ->collapsible()
                            ->schema([

                                TextEntry::make('customer_first_name')
                                    ->label(trans('First Name'))
                                    ->size(TextEntrySize::Large),

                                TextEntry::make('customer_last_name')
                                    ->label(trans('Last Name'))
                                    ->size(TextEntrySize::Large),

                                TextEntry::make('customer_email')
                                    ->label(trans('Email'))
                                    ->icon('heroicon-m-envelope')
                                    ->size(TextEntrySize::Large),

                                TextEntry::make('customer_mobile')
                                    ->label(trans('Contact Number'))
                                    ->size(TextEntrySize::Large),

                            ])
                            ->columns(),

                        Section::make()
                            ->heading(trans('Shipping Address'))
                            ->collapsible()
                            ->schema([

                                TextEntry::make('shippingAddress.address_line_1')
                                    ->label(trans('House/Unit/Flr #, Bldg Name, Blk or Lot #'))
                                    ->size(TextEntrySize::Large)
                                    ->columnSpanFull(),

                                TextEntry::make('shippingAddress.country')
                                    ->label(trans('Country'))
                                    ->size(TextEntrySize::Large),

                                TextEntry::make('shippingAddress.state')
                                    ->label(trans('State'))
                                    ->size(TextEntrySize::Large),

                                TextEntry::make('shippingAddress.city')
                                    ->label(trans('City/Province'))
                                    ->size(TextEntrySize::Large),

                                TextEntry::make('shippingAddress.zip_code')
                                    ->label(trans('Zip Code'))
                                    ->size(TextEntrySize::Large),

                            ])
                            ->columns(),

                        Section::make()
                            ->heading(trans('Billing Address'))
                            ->collapsible()
                            ->schema([

                                TextEntry::make('billingAddress.address_line_1')
                                    ->label(trans('House/Unit/Flr #, Bldg Name, Blk or Lot #'))
                                    ->size(TextEntrySize::Large)
                                    ->columnSpanFull(),

                                TextEntry::make('billingAddress.country')
                                    ->label(trans('Country'))
                                    ->size(TextEntrySize::Large),

                                TextEntry::make('billingAddress.state')
                                    ->label(trans('State'))
                                    ->size(TextEntrySize::Large),

                                TextEntry::make('billingAddress.city')
                                    ->label(trans('City/Province'))
                                    ->size(TextEntrySize::Large),

                                TextEntry::make('billingAddress.zip_code')
                                    ->label(trans('Zip Code'))
                                    ->size(TextEntrySize::Large),
                            ])
                            ->columns(),

                        Section::make()
                            ->heading(trans('Others'))
                            ->collapsible()
                            ->schema([

                                TextEntry::make('payments.paymentMethod.title')
                                    ->label(trans('Payment Method'))
                                    ->size(TextEntrySize::Large),

                                TextEntry::make('shippingMethod.title')
                                    ->label(trans('Shipping Method'))
                                    ->size(TextEntrySize::Large),
                            ])
                            ->columns(),
                    ])
                    ->columnSpan(2),

                Group::make()
                    ->schema([
                        Section::make()
                            ->heading(trans('Summary'))
                            ->collapsible()
                            ->schema([

                                TextEntry::make('status')
                                    ->hiddenLabel()
                                    ->badge()
                                    ->tooltip(trans('Status'))
                                    ->size(TextEntrySize::Large),

                                Actions::make([
                                    Action::make('edit')
                                        ->translateLabel()
                                        ->button()
                                        ->size('sm')
                                        ->modalHeading(trans('Edit Status'))
                                        ->modalWidth('xl')
                                        ->form([
                                            Forms\Components\Select::make('status')
                                                ->translateLabel()
                                                ->options(
                                                    fn (Order $record) => OrderStatuses::forOrderUpdate($record)
                                                        ->mapWithKeys(fn (OrderStatuses $case) => [
                                                            $case->value => $case->getLabel(),
                                                        ])
                                                )
//                                                ->in(
//                                                    fn (Order $record) => OrderStatuses::forOrderUpdate($record)
//                                                        ->map(fn (OrderStatuses $case) => $case->value)
//                                                )
                                                ->required()
                                                ->default(fn (Order $record) => $record->status),
                                            Forms\Components\Toggle::make('send_email')
                                                ->label(trans('Send email notification'))
                                                ->default(false)
                                                ->reactive(),
                                            Forms\Components\Textarea::make('email_remarks')
                                                ->label(trans('Remarks'))
                                                ->nullable()
                                                ->maxLength(255)
                                                ->visible(fn (Get $get): bool => $get('send_email') ?? false),
                                        ])
                                        ->action(function (array $data, Order $record) {

                                            $data['email_remarks'] ??= null;

                                            if ($data['send_email'] === true) {
                                                $fromEmail = app(OrderSettings::class)->email_sender_name;

                                                if (empty($fromEmail)) {
                                                    Notification::make()
                                                        ->title(trans('Email sender not found, please update your order settings.'))
                                                        ->warning()
                                                        ->send();

                                                    return;
                                                }
                                            }

                                            DB::transaction(function () use ($data, $record) {

                                                if ($data['status'] === OrderStatuses::CANCELLED) {
                                                    if (! in_array($record->status, [OrderStatuses::PENDING, OrderStatuses::FORPAYMENT])) {
                                                        Notification::make()
                                                            ->title(trans("You can't cancel this order."))
                                                            ->warning()
                                                            ->send();

                                                        return;
                                                    }

                                                    /** @var Payment $payment */
                                                    $payment = $record->payments->first();

                                                    $payment->update([
                                                        'status' => 'cancelled',
                                                    ]);

                                                    $result = $record->update([
                                                        'status' => $data['status'],
                                                        'cancelled_at' => now(),
                                                    ]);
                                                } else {
                                                    $result = $record->update([
                                                        'status' => $data['status'],
                                                    ]);
                                                }

                                                event(new AdminOrderStatusUpdatedEvent(
                                                    $record,
                                                    $data['send_email'],
                                                    $data['status']->value,
                                                    $data['email_remarks'],
                                                    $record->customer,
                                                ));

                                                if ($result) {
                                                    Notification::make()
                                                        ->title(trans('Order updated successfully'))
                                                        ->success()
                                                        ->send();
                                                }
                                            });
                                        })
                                        ->hidden(
                                            fn (Order $record) => $record->status == OrderStatuses::CANCELLED ||
                                            $record->status == OrderStatuses::FULFILLED
                                        ),
                                ]),

                                TextEntry::make('created_at')
                                    ->label(trans('Order Date'))
                                    ->dateTime(format: 'F d, Y g:i A')
                                    ->inlineLabel()
                                    ->columnSpanFull(),

                                Actions::make([
                                    Action::make('update_payment_status')
                                        ->label(
                                            fn (Order $record) => $record->is_paid
                                                ? trans('Unmark as paid')
                                                : trans('Mark as paid')
                                        )
//                                        ->color(
//                                            fn (Order $record) => $record->is_paid
//                                                ? 'secondary'
//                                                : 'primary'
//                                        )
                                        ->button()
                                        ->requiresConfirmation()
                                        ->action(function (array $data, Order $record, Set $set) {

                                            DB::transaction(function () use ($record) {
                                                $isPaid = ! $record->is_paid;

                                                $result = $record->update([
                                                    'is_paid' => $isPaid,
                                                ]);

                                                if (! $result) {
                                                    return;
                                                }
                                                /** @var Payment $payment */
                                                $payment = $record->payments->first();

                                                $payment->update([
                                                    'status' => $record->is_paid ? 'paid' : 'pending',
                                                ]);

                                                // $set('is_paid', $isPaid);
                                                Notification::make()
                                                    ->title(trans('Order marked successfully'))
                                                    ->success()
                                                    ->send();

                                            });
                                        }),
                                ])
                                    ->hidden(
                                        fn (Order $record) => $record->status === OrderStatuses::CANCELLED ||
                                            $record->status === OrderStatuses::FULFILLED
                                    )
                                    ->fullWidth()
                                    ->alignCenter()
                                    ->columnSpanFull(),

                                Actions::make([
                                    Action::make('view_proof_of_payment')
                                        ->translateLabel()
                                        ->button()
                                        ->outlined()
                                        ->icon('heroicon-o-eye')
                                        ->slideOver()
                                        ->modalHeading(trans('Proof of Payment'))
                                        ->size('sm')
                                        ->disabledForm(function (Order $record) {

                                            /** @var Payment $payment */
                                            $payment = $record->payments->first();

                                            return $payment->remarks !== null;
                                        })
                                        ->form(fn (Order $record) => [

                                            Forms\Components\Textarea::make('customer_message')
                                                ->translateLabel()
                                                ->model(fn () => $record->payments[0])
                                                ->disabled()
                                                ->default(fn (Payment $record) => $record->customer_message),
                                            Forms\Components\SpatieMediaLibraryFileUpload::make('customer_upload')
                                                ->translateLabel()
                                                ->model(fn () => $record->payments[0])
                                                ->collection('image')
                                                ->disabled()
                                                ->formatStateUsing(
                                                    fn (Payment $record) => $record->getMedia('image')
                                                        ->mapWithKeys(fn (Media $media) => [$media->uuid => $media->uuid])
                                                        ->toArray()
                                                ),
                                            Forms\Components\Select::make('remarks')
                                                ->label('Status')
                                                ->model(fn () => $record->payments[0])
                                                ->options([
                                                    'approved' => 'Approved',
                                                    'declined' => 'Declined',
                                                ])
                                                ->required()
                                                ->in([
                                                    'approved',
                                                    'declined',
                                                ])
                                                ->default(fn (Payment $record) => $record->remarks),
                                            Forms\Components\Textarea::make('admin_message')
                                                ->translateLabel()
                                                ->model(fn () => $record->payments[0])
                                                ->maxLength(255)
                                                ->default(fn (Payment $record) => $record->admin_message),

                                        ])
                                        ->action(function (array $data, Order $record) {
                                            /** @var Payment $payment */
                                            $payment = $record->payments->first();

                                            if ($payment->remarks !== null) {
                                                Notification::make()
                                                    ->title(trans('Invalid action.'))
                                                    ->warning()
                                                    ->send();

                                                return;
                                            }

                                            DB::transaction(function () use ($payment, $data, $record) {

                                                $result = $payment->update($data);

                                                if (! $result) {
                                                    return;
                                                }

                                                $isPaid = $data['remarks'] === 'approved';

                                                $record->update([
                                                    'is_paid' => $isPaid,
                                                ]);

                                                if ($isPaid) {
                                                    $payment->update([
                                                        'status' => 'paid',
                                                    ]);

                                                    $record->update([
                                                        'status' => OrderStatuses::PROCESSING,
                                                    ]);

                                                } else {
                                                    $payment->update([
                                                        'status' => 'cancelled',
                                                    ]);

                                                    $record->update([
                                                        'status' => OrderStatuses::CANCELLED,
                                                    ]);

                                                }

                                                $customer = $record->customer;

                                                if ($customer) {
                                                    event(new AdminOrderBankPaymentEvent(
                                                        $customer,
                                                        $record,
                                                        $data['remarks'],
                                                    ));
                                                }

                                                Notification::make()
                                                    ->title(trans('Proof of payment updated successfully'))
                                                    ->success()
                                                    ->send();

                                            });
                                        }),
                                ])
                                    ->hidden(function (Order $record) {

                                        /** @var Payment|null $payment */
                                        $payment = $record->payments->first();

                                        if ($payment === null) {
                                            return true;
                                        }

                                        if ($payment->gateway === 'bank-transfer') {
                                            return $payment->getFirstMedia('image') === null;
                                        }

                                        return true;
                                    })
                                    ->fullWidth()
                                    ->alignCenter()
                                    ->columnSpanFull(),

                                TextEntry::make('sub_total')
                                    ->translateLabel()
                                    ->inlineLabel()
                                    ->prefix(fn (Order $record) => $record->currency_symbol)
                                    ->columnSpanFull(),

                                TextEntry::make('shipping_total')
                                    ->label(trans('Total Shipping Fee'))
                                    ->inlineLabel()
                                    ->prefix(fn (Order $record) => $record->currency_symbol)
                                    ->columnSpanFull(),

                                TextEntry::make('tax_total')
                                    ->label(fn (Order $record) => trans('Tax Total ( :percent% )', [
                                        'percent' => $record->tax_percentage,
                                    ]))
                                    ->inlineLabel()
                                    ->prefix(fn (Order $record) => $record->currency_symbol)
                                    ->columnSpanFull(),

                                TextEntry::make('discount_total')
                                    ->label(trans('Total Discount'))
                                    ->inlineLabel()
                                    ->prefix(fn (Order $record) => $record->currency_symbol)
                                    ->columnSpanFull()
                                    ->hidden(fn (Order $record) => $record->discount_total == 0),

                                TextEntry::make('discount_code')
                                    ->translateLabel()
                                    ->inlineLabel()
                                    ->prefix(fn (Order $record) => $record->currency_symbol)
                                    ->columnSpanFull()
                                    ->hidden(fn (Order $record) => $record->discount_code === null),

                                TextEntry::make('total')
                                    ->label(trans('Grand Total'))
                                    ->inlineLabel()
                                    ->prefix(fn (Order $record) => $record->currency_symbol)
                                    ->columnSpanFull(),

                            ])
                            ->columns(),
                    ])
                    ->columnSpan(1),

            ])->columns(3);
    }

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
                    ->visible(fn () => tenancy()->tenant?->features()->active(AllowGuestOrder::class))
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
                    ->hidden(fn () => ! tenancy()->tenant?->features()->active(AllowGuestOrder::class))
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

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getRelations(): array
    {
        return [
            OrderResource\RelationManagers\OrderLinesRelationManager::class,
            ActivitiesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => OrderResource\Pages\ListOrders::route('/'),
            'view' => OrderResource\Pages\ViewOrder::route('/{record}'),
            'details' => OrderResource\Pages\ViewOrderDetails::route('/{record}/details'),
        ];
    }
}
