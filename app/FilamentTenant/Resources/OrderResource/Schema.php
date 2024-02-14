<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\OrderResource;

use App\Settings\OrderSettings;
use Domain\Order\Enums\OrderStatuses;
use Domain\Order\Events\AdminOrderBankPaymentEvent;
use Domain\Order\Events\AdminOrderStatusUpdatedEvent;
use Domain\Order\Models\Order;
use Domain\Payments\Models\Payment;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists\Components\Actions;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\TextEntry\TextEntrySize;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class Schema
{
    private function __construct()
    {
    }

    public static function summarySchema(): array
    {
        return [
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
        ];
    }
}
