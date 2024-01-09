<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Support;
use App\Settings\OrderSettings;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Closure;
use Domain\Customer\Models\Customer;
use Domain\Order\Enums\OrderStatuses;
use Domain\Order\Enums\OrderUserType;
use Domain\Order\Events\AdminOrderBankPaymentEvent;
use Domain\Order\Events\AdminOrderStatusUpdatedEvent;
use Domain\Order\Models\Order;
use Domain\Taxation\Enums\PriceDisplay;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

class OrderResource extends Resource
{
    use ContextualResource;

    protected static ?string $navigationGroup = 'eCommerce';

    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $recordTitleAttribute = 'reference';

    protected static function getNavigationBadge(): ?string
    {
        /** @phpstan-ignore-next-line https://filamentphp.com/docs/2.x/admin/navigation#navigation-item-badges */
        return strval(static::$model::whereIn('status', [OrderStatuses::PENDING, OrderStatuses::FORPAYMENT])->count());
    }

    /** @return Builder<\Domain\Order\Models\Order> */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['payments.paymentMethod', 'shippingMethod']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make(function (Order $record) {
                            if (tenancy()->tenant?->features()->inactive(\App\Features\ECommerce\AllowGuestOrder::class)) {
                                return 'Customer';
                            }

                            return $record->customer_id ? 'Customer (Registered)' : 'Customer (Guest)';
                        })
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Placeholder::make('first_name')
                                            ->label(trans('First Name'))
                                            ->content(fn (Order $record): string => $record->customer_first_name),
                                        Forms\Components\Placeholder::make('last_name')
                                            ->label(trans('Last Name'))
                                            ->content(fn (Order $record): string => $record->customer_last_name),
                                    ]),
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Placeholder::make('email')
                                            ->label(trans('Email'))
                                            ->content(fn (Order $record): string => $record->customer_email),
                                        Forms\Components\Placeholder::make('contact_number')
                                            ->label(trans('Contact Number'))
                                            ->content(fn (Order $record): string => $record->customer_mobile),
                                    ]),
                            ])->collapsible(),
                        Forms\Components\Section::make(trans('Shipping Address'))
                            ->schema([
                                Forms\Components\Placeholder::make('sa_line_one')
                                    ->label(trans('House/Unit/Flr #, Bldg Name, Blk or Lot #'))
                                    ->content(function (Order $record): string {
                                        /** @var \Domain\Order\Models\OrderAddress $orderShippingAddress */
                                        $orderShippingAddress = $record->shippingAddress;

                                        return $orderShippingAddress->address_line_1;
                                    }),
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Placeholder::make('sa_country')
                                            ->label(trans('Country'))
                                            ->content(function (Order $record): string {
                                                /** @var \Domain\Order\Models\OrderAddress $orderShippingAddress */
                                                $orderShippingAddress = $record->shippingAddress;

                                                return $orderShippingAddress->country;
                                            }),
                                        Forms\Components\Placeholder::make('sa_state')
                                            ->label(trans('State'))
                                            ->content(function (Order $record): string {
                                                /** @var \Domain\Order\Models\OrderAddress $orderShippingAddress */
                                                $orderShippingAddress = $record->shippingAddress;

                                                return $orderShippingAddress->state;
                                            }),
                                    ]),
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Placeholder::make('sa_city_province')
                                            ->label(trans('City/Province'))
                                            ->content(function (Order $record): string {
                                                /** @var \Domain\Order\Models\OrderAddress $orderShippingAddress */
                                                $orderShippingAddress = $record->shippingAddress;

                                                return $orderShippingAddress->city;
                                            }),
                                        Forms\Components\Placeholder::make('sa_zip_code')
                                            ->label(trans('Zip Code'))
                                            ->content(function (Order $record): string {
                                                /** @var \Domain\Order\Models\OrderAddress $orderShippingAddress */
                                                $orderShippingAddress = $record->shippingAddress;

                                                return $orderShippingAddress->zip_code;
                                            }),
                                    ]),
                            ])->collapsible(),
                        Forms\Components\Section::make(trans('Billing Address'))
                            ->schema([
                                Forms\Components\Placeholder::make('ba_line_one')
                                    ->label(trans('House/Unit/Flr #, Bldg Name, Blk or Lot #'))
                                    ->content(function (Order $record): string {
                                        /** @var \Domain\Order\Models\OrderAddress $orderBillingAddress */
                                        $orderBillingAddress = $record->billingAddress;

                                        return $orderBillingAddress->address_line_1;
                                    }),
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Placeholder::make('ba_country')
                                            ->label(trans('Country'))
                                            ->content(function (Order $record): string {
                                                /** @var \Domain\Order\Models\OrderAddress $orderBillingAddress */
                                                $orderBillingAddress = $record->billingAddress;

                                                return $orderBillingAddress->country;
                                            }),
                                        Forms\Components\Placeholder::make('ba_state')
                                            ->label(trans('State'))
                                            ->content(function (Order $record): string {
                                                /** @var \Domain\Order\Models\OrderAddress $orderBillingAddress */
                                                $orderBillingAddress = $record->billingAddress;

                                                return $orderBillingAddress->state;
                                            }),
                                    ]),
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Placeholder::make('ba_city_province')
                                            ->label(trans('City/Province'))
                                            ->content(function (Order $record): string {
                                                /** @var \Domain\Order\Models\OrderAddress $orderBillingAddress */
                                                $orderBillingAddress = $record->billingAddress;

                                                return $orderBillingAddress->city;
                                            }),
                                        Forms\Components\Placeholder::make('ba_zip_code')
                                            ->label(trans('Zip Code'))
                                            ->content(function (Order $record): string {
                                                /** @var \Domain\Order\Models\OrderAddress $orderBillingAddress */
                                                $orderBillingAddress = $record->billingAddress;

                                                return $orderBillingAddress->zip_code;
                                            }),
                                    ]),
                            ])->collapsible(),
                        Forms\Components\Section::make(trans('Others'))
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([

                                        Forms\Components\Placeholder::make('payment_method')
                                            ->label(trans('Payment Method'))
                                            ->content(function (Order $record): string {
                                                /** @var \Domain\Payments\Models\Payment $payment */
                                                $payment = $record->payments->first();

                                                return $payment->paymentMethod?->title ?? '';
                                            }),
                                        Forms\Components\Placeholder::make('shipping_method')
                                            ->label(trans('Shipping Method'))
                                            ->content(function (Order $record): string {
                                                if ($record->shipping_method_id) {
                                                    /** @var \Domain\ShippingMethod\Models\ShippingMethod $shippingMethod */
                                                    $shippingMethod = $record->shippingMethod;

                                                    return $shippingMethod->title;
                                                }

                                                return '';
                                            }),
                                    ]),
                            ])->collapsible(),
                    ])->columnSpan(2),
                self::summaryCard(),
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
                    ->hidden(function () {
                        return ! tenancy()->tenant?->features()->active(\App\Features\ECommerce\AllowGuestOrder::class);
                    })
                    ->alignLeft()
                    ->label(trans('Customer Type'))
                    ->formatStateUsing(function (?string $state) {
                        return $state ? 'Registered'
                            : 'Guest';
                    }),
                Tables\Columns\TextColumn::make('customer_name')
                    ->label(trans('Customer'))
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query
                            ->orderBy('customer_first_name', $direction);
                    })
                    ->formatStateUsing(function ($record) {
                        return Str::limit($record->customer_first_name.' '.$record->customer_last_name, 30);
                    })
                    ->searchable(query: function (Builder $query, string $search) {
                        $query->where('customer_first_name', 'like', "%{$search}%")
                            ->orWhere('customer_last_name', 'like', "%{$search}%");
                    })->wrap(),
                Tables\Columns\TextColumn::make('tax_total')
                    ->alignRight()
                    ->label(trans('Tax Total'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(function (Order $record) {
                        return $record->currency_symbol.' '.number_format((float) $record->tax_total, 2, '.', ',');
                    }),
                Tables\Columns\TextColumn::make('total')
                    ->formatStateUsing(function (Order $record) {
                        return $record->currency_symbol.' '.number_format((float) $record->total, 2, '.', ',');
                    })
                    ->alignRight()
                    ->label(trans('Total'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label(trans('Payment Method'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(function (Order $record) {
                        /** @var \Domain\Payments\Models\Payment $payment */
                        $payment = $record->payments->first();

                        return Str::limit($payment->paymentMethod->title ?? '', 30);
                    }),
                Tables\Columns\TextColumn::make('shipping_method')
                    ->label(trans('Shipping Method'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(function (Order $record) {
                        if ($record->shipping_method_id) {
                            /** @var \Domain\ShippingMethod\Models\ShippingMethod $shippingMethod */
                            $shippingMethod = $record->shippingMethod;

                            return Str::limit($shippingMethod->title, 30);
                        }

                        return '';
                    }),
                Tables\Columns\IconColumn::make('is_paid')
                    ->label(trans('Paid'))
                    ->alignRight()
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->sortable()
                    ->label(trans('Order Date'))
                    ->dateTime(timezone: Auth::user()?->timezone),
                Tables\Columns\BadgeColumn::make('status')
                    ->label(trans('Status'))
                    ->formatStateUsing(function ($state) {
                        if ($state == OrderStatuses::FORPAYMENT->value) {
                            return 'For Payment';
                        } elseif ($state == OrderStatuses::FORAPPROVAL->value) {
                            return 'For Approval';
                        }

                        return ucfirst($state);
                    })
                    ->color(function ($state) {
                        return match ($state) {
                            OrderStatuses::FORAPPROVAL->value => 'warning',
                            OrderStatuses::REFUNDED->value,
                            OrderStatuses::CANCELLED->value => 'danger',
                            OrderStatuses::FULFILLED->value,
                            OrderStatuses::DELIVERED->value => 'success',
                            OrderStatuses::PACKED->value,
                            OrderStatuses::PROCESSING->value,
                            OrderStatuses::SHIPPED->value => 'primary',
                            default => 'secondary',
                        };
                    })
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
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
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
                    ->hidden(function () {
                        return ! tenancy()->tenant?->features()->active(\App\Features\ECommerce\AllowGuestOrder::class);
                    })
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
            ->defaultSort('id', 'DESC');
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
            'details' => OrderResource\Pages\ViewOrderDetails::route('/details/{record}'),
        ];
    }

    public static function summaryCard(): Forms\Components\Section
    {
        return Forms\Components\Section::make(trans('Summary'))
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Support\BadgeLabel::make(trans('status'))
                            ->formatStateUsing(function (string $state): string {
                                if ($state == OrderStatuses::FORPAYMENT->value) {
                                    return trans('For Payment');
                                } elseif ($state == OrderStatuses::FORAPPROVAL->value) {
                                    return 'For Approval';
                                }

                                return trans(ucfirst($state));
                            })
                            ->color(function ($state) {
                                $newState = str_replace(' ', '_', strtolower($state));

                                return match ($newState) {
                                    OrderStatuses::FORAPPROVAL->value => 'warning',
                                    OrderStatuses::REFUNDED->value,
                                    OrderStatuses::CANCELLED->value => 'danger',
                                    OrderStatuses::FULFILLED->value,
                                    OrderStatuses::DELIVERED->value => 'success',
                                    OrderStatuses::PROCESSING->value,
                                    OrderStatuses::PACKED->value,
                                    OrderStatuses::SHIPPED->value => 'primary',
                                    default => 'secondary',
                                };
                            })
                            ->inline()
                            ->alignLeft(),
                        self::summaryEditButton(),
                    ]),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Support\TextLabel::make('')
                            ->label(trans('Order Date'))
                            ->alignLeft()
                            ->size('md')
                            ->inline()
                            ->readOnly(),
                        Support\TextLabel::make('created_at')
                            ->alignRight()
                            ->size('md')
                            ->inline()
                            ->formatStateUsing(function ($state) {
                                /** @var string */
                                $timeZone = Auth::user()?->timezone;

                                $formattedState = Carbon::parse($state)
                                    ->setTimezone($timeZone)
                                    ->translatedFormat('F d, Y g:i A');

                                return $formattedState;
                            }),
                    ]),
                self::summaryMarkAsPaidButton(),
                self::summaryProofOfPaymentButton(),
                Support\Divider::make(''),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Support\TextLabel::make('')
                            ->label(trans('Subtotal'))
                            ->alignLeft()
                            ->size('md')
                            ->inline()
                            ->readOnly(),
                        Support\TextLabel::make('sub_total')
                            ->formatStateUsing(function (Order $record) {
                                return $record->currency_symbol.' '.number_format($record->sub_total, 2, '.', ',');
                            })
                            ->alignRight()
                            ->size('md')
                            ->inline(),
                    ]),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Support\TextLabel::make('')
                            ->label(trans('Total Shipping Fee'))
                            ->alignLeft()
                            ->size('md')
                            ->inline()
                            ->readOnly(),
                        Support\TextLabel::make('shipping_total')
                            ->alignRight()
                            ->size('md')
                            ->inline()
                            ->formatStateUsing(function (Order $record) {
                                return $record->currency_symbol.' '.number_format($record->shipping_total, 2, '.', ',');
                            }),
                    ]),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Support\TextLabel::make('')
                            ->label(function (Order $record) {
                                return trans("Tax Total ( $record->tax_percentage% )");
                            })
                            ->alignLeft()
                            ->size('md')
                            ->inline()
                            ->readOnly(),
                        Support\TextLabel::make('tax_total')
                            ->alignRight()
                            ->size('md')
                            ->inline()
                            ->formatStateUsing(function (Order $record) {
                                if ($record->tax_total) {
                                    return $record->currency_symbol.' '.number_format($record->tax_total, 2, '.', ',');
                                }

                                return $record->currency_symbol.' '.'0.00';
                            }),
                    ])->hidden(function (Order $record) {
                        return $record->tax_display == PriceDisplay::INCLUSIVE;
                    }),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Support\TextLabel::make('')
                            ->label(trans('Total Discount'))
                            ->alignLeft()
                            ->size('md')
                            ->inline()
                            ->readOnly(),
                        Support\TextLabel::make('discount_total')
                            ->alignRight()
                            ->size('md')
                            ->inline()
                            ->formatStateUsing(function (Order $record) {
                                if ($record->discount_total) {
                                    return $record->currency_symbol.' '.number_format($record->discount_total, 2, '.', ',');
                                }

                                return $record->currency_symbol.' '.'0.00';
                            }),
                    ])->hidden(function (Order $record) {
                        return (bool) ($record->discount_total == 0);
                    }),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Support\TextLabel::make('')
                            ->label(trans('Discount Code'))
                            ->alignLeft()
                            ->size('md')
                            ->inline()
                            ->readOnly(),
                        Support\TextLabel::make('discount_code')
                            ->alignRight()
                            ->size('md')
                            ->inline(),
                    ])
                    ->hidden(function (Order $record) {
                        return is_null($record->discount_code) ? true : false;
                    }),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Support\TextLabel::make('')
                            ->label(trans('Grand Total'))
                            ->alignLeft()
                            ->size('md')
                            ->color('primary')
                            ->inline()
                            ->readOnly(),
                        Support\TextLabel::make('total')
                            ->alignRight()
                            ->size('md')
                            ->color('primary')
                            ->inline()
                            ->formatStateUsing(function (Order $record) {
                                return $record->currency_symbol.' '.number_format($record->total, 2, '.', ',');
                            }),
                    ]),
            ])->columnSpan(1);
    }

    private static function summaryEditButton(): Support\ButtonAction
    {
        return Support\ButtonAction::make('Edit')
            ->execute(function (Order $record, Closure $get, Closure $set) {
                return Forms\Components\Actions\Action::make(trans('edit'))
                    ->color('primary')
                    ->label('Edit')
                    ->size('sm')
                    ->modalHeading(trans('Edit Status'))
                    ->modalWidth('xl')
                    ->form([
                        Forms\Components\Select::make('status_options')
                            ->label('')
                            ->options(function () use ($record) {
                                $options = [
                                    OrderStatuses::PENDING->value => trans('Pending'),
                                    OrderStatuses::FORPAYMENT->value => trans('For Payment'),
                                    OrderStatuses::FORAPPROVAL->value => trans('For Approval'),
                                    OrderStatuses::PROCESSING->value => trans('Processing'),
                                    OrderStatuses::CANCELLED->value => trans('Cancelled'),
                                    OrderStatuses::REFUNDED->value => trans('Refunded'),
                                    OrderStatuses::PACKED->value => trans('Packed'),
                                    OrderStatuses::SHIPPED->value => trans('Shipped'),
                                    OrderStatuses::DELIVERED->value => trans('Delivered'),
                                ];

                                if ($record->is_paid) {
                                    $options[OrderStatuses::FULFILLED->value] = trans('Fulfilled');
                                }

                                return $options;
                            })
                            ->disablePlaceholderSelection()
                            ->formatStateUsing(function () use ($record) {
                                return $record->status;
                            }),
                        Forms\Components\Toggle::make('send_email')
                            ->label(trans('Send email notification'))
                            ->default(false)
                            ->reactive(),
                        Forms\Components\Textarea::make('email_remarks')
                            ->maxLength(255)
                            ->label(trans('Remarks'))
                            ->visible(fn (Closure $get) => $get('send_email') == true)
                            ->dehydrateStateUsing(function (?string $state) use ($get) {
                                if (filled($state) && $get('send_email') == true) {
                                    return $state;
                                }

                                return null;
                            }),
                    ])
                    ->action(
                        function (array $data, $livewire) use ($record, $set) {

                            DB::transaction(function () use ($data, $livewire, $record, $set) {
                                $shouldSendEmail = $livewire->mountedFormComponentActionData['send_email'];
                                $emailRemarks = $livewire->mountedFormComponentActionData['email_remarks'];

                                if ($shouldSendEmail) {
                                    $fromEmail = app(OrderSettings::class)->email_sender_name;

                                    if (empty($fromEmail)) {
                                        Notification::make()
                                            ->title(trans('Email sender not found, please update your order settings.'))
                                            ->warning()
                                            ->send();

                                        return;
                                    }
                                }

                                $status = $data['status_options'];
                                $updateData = ['status' => $status];

                                if ($status == OrderStatuses::CANCELLED->value) {
                                    if (! in_array($record->status, [OrderStatuses::PENDING, OrderStatuses::FORPAYMENT])) {
                                        Notification::make()
                                            ->title(trans("You can't cancel this order."))
                                            ->warning()
                                            ->send();

                                        return;
                                    }

                                    $updateData['cancelled_at'] = now();

                                    $order = $record;

                                    /** @var \Domain\Payments\Models\Payment $payment */
                                    $payment = $order->payments->first();

                                    $payment->update([
                                        'status' => 'cancelled',
                                    ]);
                                }

                                $result = $record->update($updateData);

                                if ($result) {
                                    $set('status', ucfirst($data['status_options']));
                                    Notification::make()
                                        ->title(trans('Order updated successfully'))
                                        ->success()
                                        ->send();
                                }

                                if ($record->customer_id) {
                                    //registered
                                    $customer = Customer::where('id', $record->customer_id)->first();
                                    if ($customer) {
                                        event(new AdminOrderStatusUpdatedEvent(
                                            $record,
                                            $shouldSendEmail,
                                            $data['status_options'],
                                            $emailRemarks,
                                            $customer,
                                        ));
                                    }
                                } else {
                                    //guest
                                    event(new AdminOrderStatusUpdatedEvent(
                                        $record,
                                        $shouldSendEmail,
                                        $data['status_options'],
                                        $emailRemarks
                                    ));
                                }
                            });
                        }
                    );
            })
            ->disableLabel()
            ->columnSpan(1)
            ->alignRight()
            ->size('sm')
            ->hidden(function (Order $record) {
                return $record->status == OrderStatuses::CANCELLED ||
                    $record->status == OrderStatuses::FULFILLED;
            });
    }

    private static function summaryMarkAsPaidButton(): Support\ButtonAction
    {
        return Support\ButtonAction::make(trans('mark_as_paid'))
            ->disableLabel()
            ->execute(function (Order $record, Closure $set) {
                $order = $record;

                return Forms\Components\Actions\Action::make('mark_as_paid')
                    ->color(function () use ($order) {
                        if ($order->is_paid) {
                            return 'secondary';
                        }

                        return 'primary';
                    })
                    ->label(function () use ($order) {
                        if ($order->is_paid) {
                            return trans('Unmark as paid');
                        }

                        return trans('Mark as paid');
                    })
                    ->size('sm')
                    ->action(function () use ($order, $set) {
                        DB::transaction(function () use ($order, $set) {
                            $isPaid = ! $order->is_paid;

                            $result = $order->update([
                                'is_paid' => $isPaid,
                            ]);

                            if ($result) {
                                /** @var \Domain\Payments\Models\Payment $payment */
                                $payment = $order->payments->first();

                                if ($order->is_paid) {
                                    $payment->update([
                                        'status' => 'paid',
                                    ]);
                                } else {
                                    $payment->update([
                                        'status' => 'pending',
                                    ]);
                                }

                                $set('is_paid', $isPaid);
                                Notification::make()
                                    ->title(trans('Order marked successfully'))
                                    ->success()
                                    ->send();
                            }
                        });
                    })
                    ->requiresConfirmation();
            })
            ->fullWidth()
            ->size('md')
            ->hidden(function (Order $record) {
                return $record->status == OrderStatuses::CANCELLED ||
                    $record->status == OrderStatuses::FULFILLED;
            });
    }

    private static function summaryProofOfPaymentButton(): Support\ButtonAction
    {
        return Support\ButtonAction::make('proof_of_payment')
            ->disableLabel()
            ->execute(function (Order $record, Closure $set) {
                $footerActions = self::showProofOfPaymentActions($record, $set);

                $order = $record;

                /** @var \Domain\Payments\Models\Payment $payment */
                $payment = $order->payments->first();

                if (! is_null($payment->remarks)) {
                    return $footerActions->modalActions([])->disableForm();
                }

                return $footerActions;
            })
            ->fullWidth()
            ->size('md')
            ->hidden(function (Order $record) {
                $order = $record;

                /** @var \Domain\Payments\Models\Payment $payment */
                $payment = $order->payments->first();

                if ($payment->gateway == 'bank-transfer') {
                    return (bool) (empty($order->payments->first()?->getFirstMediaUrl('image')));
                }

                return true;
            });
    }

    private static function showProofOfPaymentActions(Order $record, Closure $set): Forms\Components\Actions\Action
    {
        $order = $record;

        return Forms\Components\Actions\Action::make('proof_of_payment')
            ->color('secondary')
            ->label(trans('View Proof of payment'))
            ->size('sm')
            ->action(function (array $data) use ($order, $set) {
                DB::transaction(function () use ($data, $order, $set) {

                    $paymentRemarks = $data['payment_remarks'];
                    $message = $data['message'];

                    /** @var \Domain\Payments\Models\Payment $payment */
                    $payment = $order->payments->first();

                    if (! is_null($payment->remarks)) {
                        Notification::make()
                            ->title(trans('Invalid action.'))
                            ->warning()
                            ->send();

                        return;
                    }

                    $result = $payment->update([
                        'remarks' => $paymentRemarks,
                        'admin_message' => $message,
                    ]);

                    if ($result) {
                        $isPaid = $paymentRemarks == 'approved' ? true : false;

                        $order->update([
                            'is_paid' => $isPaid,
                        ]);

                        if ($isPaid) {
                            $payment->update([
                                'status' => 'paid',
                            ]);

                            $order->update([
                                'status' => OrderStatuses::PROCESSING,
                            ]);

                            $set('status', ucfirst(OrderStatuses::PROCESSING->value));
                        } else {
                            $payment->update([
                                'status' => 'cancelled',
                            ]);

                            $order->update([
                                'status' => OrderStatuses::CANCELLED,
                            ]);

                            $set('status', ucfirst(OrderStatuses::CANCELLED->value));
                        }

                        Notification::make()
                            ->title(trans('Proof of payment updated successfully'))
                            ->success()
                            ->send();

                        $customer = Customer::where('id', $order->customer_id)->first();

                        if ($customer) {
                            event(new AdminOrderBankPaymentEvent(
                                $customer,
                                $order,
                                $paymentRemarks,
                            ));
                        }
                    }
                });
            })
            ->modalHeading(trans('Proof of Payment'))
            ->modalWidth('lg')
            ->form([
                Forms\Components\Textarea::make('customer_message')
                    ->label(trans('Customer Message'))
                    ->formatStateUsing(function () use ($order) {
                        /** @var \Domain\Payments\Models\Payment $payment */
                        $payment = $order->payments->first();

                        return $payment->customer_message;
                    })
                    ->disabled(),

                Forms\Components\FileUpload::make('bank_proof_image')
                    ->label(trans('Customer Upload'))
                    ->formatStateUsing(function () use ($order) {
                        return $order->payments->first()?->getMedia('image')
                            ->mapWithKeys(fn (Media $file) => [$file->uuid => $file->uuid])
                            ->toArray() ?? [];
                    })
                    ->hidden(function () use ($order) {
                        return (bool) (empty($order->payments->first()?->getFirstMediaUrl('image')));
                    })
                    ->image()
                    ->getUploadedFileUrlUsing(static function (
                        Forms\Components\FileUpload $component,
                        string $file
                    ): ?string {
                        $mediaClass = config('media-library.media_model', Media::class);

                        /** @var ?Media $media */
                        $media = $mediaClass::findByUuid($file);

                        if ($component->getVisibility() === 'private') {
                            try {
                                return $media?->getTemporaryUrl(now()->addMinutes(5));
                            } catch (Throwable) {
                            }
                        }

                        return $media?->getUrl();
                    })
                    ->disabled(),
                Forms\Components\Select::make('payment_remarks')
                    ->label('Status')
                    ->options([
                        'approved' => 'Approved',
                        'declined' => 'Declined',
                    ])
                    ->disablePlaceholderSelection(function () use ($order) {
                        /** @var \Domain\Payments\Models\Payment $payment */
                        $payment = $order->payments->first();

                        return in_array($payment->remarks, ['approved', 'declined']);
                    })
                    ->formatStateUsing(function () use ($order) {
                        /** @var \Domain\Payments\Models\Payment $payment */
                        $payment = $order->payments->first();

                        return $payment->remarks;
                    }),
                Forms\Components\Textarea::make('message')
                    ->maxLength(255)
                    ->label(trans('Admin Message'))
                    ->formatStateUsing(function () use ($order) {
                        /** @var \Domain\Payments\Models\Payment $payment */
                        $payment = $order->payments->first();

                        return $payment->admin_message;
                    }),
            ])
            ->slideOver()
            ->icon('heroicon-s-eye');
    }
}
