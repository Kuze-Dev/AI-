<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\FilamentTenant\Support;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Domain\Order\Models\Order;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Resources\Form;
use Filament\Forms;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Closure;
use Domain\Customer\Models\Customer;
use Domain\Discount\Models\Discount;
use Domain\Discount\Models\DiscountLimit;
use Domain\Order\Enums\OrderStatuses;
use Domain\Order\Events\AdminOrderStatusUpdatedEvent;
use Domain\Taxation\Enums\PriceDisplay;
use Filament\Notifications\Notification;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

class OrderResource extends Resource
{
    use ContextualResource;

    protected static ?string $navigationGroup = 'eCommerce';

    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static function getNavigationBadge(): ?string
    {
        return strval(static::$model::where('status', OrderStatuses::PENDING)->count());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Customer')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Placeholder::make('first_name')->label('First Name')
                                            ->content(fn (Order $record): ?string => $record->customer_first_name),
                                        Forms\Components\Placeholder::make('last_name')->label('Last Name')
                                            ->content(fn (Order $record): ?string => $record->customer_last_name),
                                    ]),
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Placeholder::make('email')->label('Email')
                                            ->content(fn (Order $record): ?string => $record->customer_email),
                                        Forms\Components\Placeholder::make('contact_number')->label('Contact Number')
                                            ->content(fn (Order $record): ?string => $record->customer_mobile),
                                    ]),
                            ])->collapsible(),
                        Forms\Components\Section::make('Shipping Address')
                            ->schema([
                                Forms\Components\Placeholder::make('sa_line_one')->label('House/Unit/Flr #, Bldg Name, Blk or Lot #')
                                    ->content(fn (Order $record): ?string => $record->shippingAddress->address_line_1),
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Placeholder::make('sa_country')->label('Country')
                                            ->content(fn (Order $record): ?string => $record->shippingAddress->country),
                                        Forms\Components\Placeholder::make('sa_state')->label('State')
                                            ->content(fn (Order $record): ?string => $record->shippingAddress->state),
                                    ]),
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Placeholder::make('sa_city_province')->label('City/Province')
                                            ->content(fn (Order $record): ?string => $record->shippingAddress->city),
                                        Forms\Components\Placeholder::make('sa_zip_code')->label('Zip Code')
                                            ->content(fn (Order $record): ?string => $record->shippingAddress->zip_code),
                                    ]),
                            ])->collapsible(),
                        Forms\Components\Section::make('Billing Address')
                            ->schema([
                                Forms\Components\Placeholder::make('ba_line_one')->label('House/Unit/Flr #, Bldg Name, Blk or Lot #')
                                    ->content(fn (Order $record): ?string => $record->billingAddress->address_line_1),
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Placeholder::make('ba_country')->label('Country')
                                            ->content(fn (Order $record): ?string => $record->billingAddress->country),
                                        Forms\Components\Placeholder::make('ba_state')->label('State')
                                            ->content(fn (Order $record): ?string => $record->billingAddress->state),
                                    ]),
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Placeholder::make('ba_city_province')->label('City/Province')
                                            ->content(fn (Order $record): ?string => $record->billingAddress->city),
                                        Forms\Components\Placeholder::make('ba_zip_code')->label('Zip Code')
                                            ->content(fn (Order $record): ?string => $record->billingAddress->zip_code),
                                    ]),
                            ])->collapsible(),
                        Forms\Components\Section::make('Payment Method')
                            ->schema([
                                Forms\Components\FileUpload::make('payment_image')
                                    ->formatStateUsing(function (Order $record) {
                                        $orderPayment = Order::with('payments.paymentMethod')->find($record->id);

                                        return $orderPayment->payments->first()->paymentMethod?->getMedia('logo')
                                            ->mapWithKeys(fn (Media $file) => [$file->uuid => $file->uuid])
                                            ->toArray() ?? [];
                                    })
                                    ->disabled()
                                    ->disableLabel()
                                    ->image()
                                    ->imagePreviewHeight('150')
                                    ->getUploadedFileUrlUsing(static function (Forms\Components\FileUpload $component, string $file): ?string {
                                        $mediaClass = config('media-library.media_model', Media::class);

                                        /** @var ?Media $media */
                                        $media = $mediaClass::findByUuid($file);

                                        if ($component->getVisibility() === 'private') {
                                            try {
                                                return $media?->getTemporaryUrl(now()->addMinutes(5));
                                            } catch (Throwable $exception) {
                                                // This driver does not support creating temporary URLs.
                                            }
                                        }

                                        return $media?->getUrl();
                                    }),

                                // Forms\Components\Placeholder::make('card_info')->label('Card Info')
                                //     ->content(fn (Order $record): ?string => '*************Test'),
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
                Tables\Columns\TextColumn::make('customer_name')
                    ->label(trans('Customer'))
                    ->sortable()
                    ->formatStateUsing(function ($record) {
                        return $record->customer_first_name . ' ' . $record->customer_last_name;
                    }),
                Tables\Columns\TextColumn::make('tax_total')
                    ->label(trans('Tax Total'))
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('total')
                    ->label(trans('Total'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('shipping_method')
                    ->label(trans('Shipping Method'))
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_paid')
                    ->label(trans('Paid'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->sortable()
                    ->label(trans('Order Date'))
                    ->dateTime(timezone: Auth::user()?->timezone),
                Tables\Columns\BadgeColumn::make('status')
                    ->label(trans('Status'))
                    ->formatStateUsing(function ($state) {
                        return ucfirst($state);
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
                            $indicators['created_from'] = 'Created from ' . Carbon::parse($data['created_from'])->toFormattedDateString();
                        }

                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Created until ' . Carbon::parse($data['created_until'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),

                Tables\Filters\SelectFilter::make('status')->label(trans('Status'))
                    ->options([
                        OrderStatuses::PENDING->value => trans('Pending'),
                        OrderStatuses::CANCELLED->value => trans('Cancelled'),
                        OrderStatuses::REFUNDED->value => trans('Refunded'),
                        OrderStatuses::PACKED->value => trans('Packed'),
                        OrderStatuses::SHIPPED->value => trans('Shipped'),
                        OrderStatuses::DELIVERED->value => trans('Delivered'),
                        OrderStatuses::FULFILLED->value => trans('Fulfilled'),
                        OrderStatuses::FORPAYMENT->value => trans('For Payment'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        $query->when(filled($data['value']), function (Builder $query) use ($data) {
                            $isForPayment = $data['value'] == OrderStatuses::FORPAYMENT->value;

                            if ($isForPayment) {
                                return $query->whereHas('payments', function ($subQuery) {
                                    $subQuery->where(function ($query) {
                                        $query->whereIn('gateway', ['paypal', 'bank-transfer', 'stripe']);
                                    })->where('status', 'pending');
                                })->where('is_paid', false);
                            }

                            return $query->where('status', $data['value']);
                        });
                    })
                    ->attribute('status')
            ])
            ->bulkActions([
                // Tables\Actions\BulkAction::make('export')
                //     ->action(function (Collection $records) {
                //         return Excel::download(new ExportCollection($records), 'orders.csv');
                //     })
                //     ->color('primary')
                //     ->icon('heroicon-o-check')
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->color('primary'),
            ])
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

    public static function summaryCard()
    {
        return Forms\Components\Section::make('Summary')
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Support\BadgeLabel::make('status')
                            ->inline()
                            ->alignLeft(),
                        Support\ButtonAction::make('Edit')
                            ->execute(function (Closure $get, Closure $set) {
                                return Forms\Components\Actions\Action::make('edit')
                                    ->color('primary')
                                    ->label('Edit')
                                    ->size('sm')
                                    ->modalHeading('Edit Status')
                                    ->modalWidth('xl')
                                    ->form([
                                        Forms\Components\Select::make('status_options')
                                            ->label('')
                                            ->options([
                                                'Pending' => 'Pending',
                                                'Cancelled' => 'Cancelled',
                                                'Refunded' => 'Refunded',
                                                'Packed' => 'Packed',
                                                'Shipped' => 'Shipped',
                                                'Delivered' => 'Delivered',
                                                'Fulfilled' => 'Fulfilled',
                                            ])
                                            ->disablePlaceholderSelection()
                                            ->formatStateUsing(function () use ($get) {
                                                return $get('status');
                                            }),
                                        Forms\Components\Toggle::make('send_email')
                                            ->label('Send email notification')->default(false)
                                            ->reactive(),
                                        Forms\Components\Textarea::make('email_remarks')->label('Remarks')
                                            ->visible(fn (Closure $get) => $get('send_email') == true)
                                            ->dehydrateStateUsing(function (string|null $state) use ($get) {
                                                if (filled($state) && $get('send_email') == true) {
                                                    return $state;
                                                }

                                                return null;
                                            }),
                                    ])
                                    ->action(
                                        function (array $data, $livewire) use ($get, $set) {

                                            $order = Order::find($get('id'));

                                            $status = $data['status_options'];
                                            $updateData = ['status' => $status];

                                            if ($status == 'Cancelled') {
                                                if ($order->status == OrderStatuses::PACKED) {
                                                    Notification::make()
                                                        ->title("You can't cancel this order, its already packed.")
                                                        ->warning()
                                                        ->send();

                                                    return;
                                                }
                                                $updateData['cancelled_at'] = now(Auth::user()?->timezone);

                                                if ($order->discount_code != null) {
                                                    DiscountLimit::whereOrderId($order->id)->delete();
                                                    $discount = Discount::whereCode($order->discount_code)->first();

                                                    $discount->update([
                                                        'max_uses' => $discount->max_uses + 1,
                                                    ]);
                                                }
                                            }

                                            $result = $order->update($updateData);

                                            if ($result) {
                                                $set('status', $data['status_options']);
                                                Notification::make()
                                                    ->title('Order updated successfully')
                                                    ->success()
                                                    ->send();
                                            }

                                            $shouldSendEmail = $livewire->mountedFormComponentActionData['send_email'];
                                            $emailRemarks = $livewire->mountedFormComponentActionData['email_remarks'];

                                            $customer = Customer::where('id', $order->customer_id)->first();

                                            if ($customer) {
                                                event(new AdminOrderStatusUpdatedEvent(
                                                    $customer,
                                                    $order,
                                                    $shouldSendEmail,
                                                    $data['status_options'],
                                                    $emailRemarks
                                                ));
                                            }
                                        }
                                    );
                            })->disableLabel()->columnSpan(1)->alignRight()->size('sm')
                            ->hidden(function (Order $record) {
                                return $record->status == OrderStatuses::CANCELLED || $record->status == OrderStatuses::FULFILLED;
                            }),
                    ]),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Support\TextLabel::make('')->label('Order Date')->alignLeft()->size('md')->inline()->readOnly(),
                        Support\TextLabel::make('created_at')->alignRight()->size('md')->inline()
                            ->formatStateUsing(function ($state) {
                                $format ??= config('tables.date_format');
                                $formattedState = Carbon::parse($state)
                                    ->setTimezone(Auth::user()?->timezone)
                                    ->translatedFormat($format);

                                return $formattedState;
                            }),
                    ]),
                Support\ButtonAction::make('mark_as_paid')
                    ->disableLabel()
                    ->execute(function (Closure $get, Closure $set) {
                        return Forms\Components\Actions\Action::make('mark_as_paid')
                            ->color(function () use ($get) {
                                if ($get('is_paid')) {
                                    return 'secondary';
                                }

                                return 'primary';
                            })
                            ->label(function () use ($get) {
                                if ($get('is_paid')) {
                                    return 'Unmark as paid';
                                }

                                return 'Mark as paid';
                            })
                            ->size('sm')
                            ->action(function () use ($get, $set) {
                                $order = Order::with('payments')->find($get('id'));

                                $isPaid = !$order->is_paid;

                                $result = $order->update([
                                    'is_paid' => $isPaid,
                                ]);

                                if ($result) {
                                    if ($order->is_paid) {
                                        $order->payments->first()->update([
                                            'status' => 'paid',
                                        ]);
                                    } else {
                                        $order->payments->first()->update([
                                            'status' => 'pending',
                                        ]);
                                    }

                                    $set('is_paid', $isPaid);
                                    Notification::make()
                                        ->title('Order marked successfully')
                                        ->success()
                                        ->send();
                                }
                            })
                            ->requiresConfirmation();
                    })->fullWidth()->size('md')
                    ->hidden(function (Order $record) {
                        return $record->status == OrderStatuses::CANCELLED || $record->status == OrderStatuses::FULFILLED;
                    }),
                Support\ButtonAction::make('proof_of_payment')
                    ->disableLabel()
                    ->execute(function () {
                        return Forms\Components\Actions\Action::make('proof_of_payment')
                            ->color('secondary')
                            ->label('View Proof of payment')
                            ->size('sm')
                            ->action(function () {
                            })
                            ->modalHeading('Proof of Payment')
                            ->modalWidth('lg')
                            ->form([
                                Forms\Components\FileUpload::make('bank_proof_image')->label('Customer Upload')
                                    ->formatStateUsing(function (Order $record) {
                                        $orderPayment = Order::with('payments')->find($record->id);

                                        return $orderPayment->payments->first()?->getMedia('image')
                                            ->mapWithKeys(fn (Media $file) => [$file->uuid => $file->uuid])
                                            ->toArray() ?? [];
                                    })
                                    ->hidden(function (Order $record) {
                                        $orderPayment = Order::with('payments')->find($record->id);

                                        return (bool) (empty($orderPayment->payments->first()?->getFirstMediaUrl('image')));
                                    })
                                    // ->multiple()
                                    ->image()
                                    ->getUploadedFileUrlUsing(static function (Forms\Components\FileUpload $component, string $file): ?string {
                                        $mediaClass = config('media-library.media_model', Media::class);

                                        /** @var ?Media $media */
                                        $media = $mediaClass::findByUuid($file);

                                        if ($component->getVisibility() === 'private') {
                                            try {
                                                return $media?->getTemporaryUrl(now()->addMinutes(5));
                                            } catch (Throwable $exception) {
                                            }
                                        }

                                        return $media?->getUrl();
                                    })->disabled(),
                                Forms\Components\Select::make('payment_status')
                                    ->label('')
                                    ->options([
                                        'Approved' => 'Approved',
                                        'Declined' => 'Declined',
                                    ]),
                                Forms\Components\Textarea::make('Message')
                                    ->formatStateUsing(function (Order $record) {
                                        $orderPayment = Order::with('payments')->find($record->id);

                                        return $orderPayment->payments->first()->message;
                                    }),
                            ])
                            ->slideOver()
                            ->icon('heroicon-s-eye');
                    })->fullWidth()->size('md')
                    ->hidden(function (Order $record) {
                        $orderPayment = Order::with('payments')->find($record->id);

                        return $orderPayment->payments->first()->gateway != 'bank-transfer';
                    }),
                Support\Divider::make(''),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Support\TextLabel::make('')->label(function (Order $record) {
                            if ($record->tax_display == PriceDisplay::INCLUSIVE) {
                                return 'Subtotal ' . ' (Tax Included)';
                            }

                            return 'Subtotal';
                        })->alignLeft()->size('md')->inline()->readOnly(),
                        Support\TextLabel::make('sub_total')->alignRight()->size('md')->inline(),
                    ]),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Support\TextLabel::make('')->label('Total Shipping Fee')->alignLeft()->size('md')->inline()->readOnly(),
                        Support\TextLabel::make('shipping_total')->alignRight()->size('md')->inline()
                            ->formatStateUsing(function (Order $record) {
                                return number_format($record->shipping_total, 2, '.', '');
                            }),
                    ]),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Support\TextLabel::make('')->label(function (Order $record) {
                            return "Tax Total ( $record->tax_percentage% )";
                        })->alignLeft()->size('md')->inline()->readOnly(),
                        Support\TextLabel::make('tax_total')->alignRight()->size('md')->inline()
                            ->formatStateUsing(function (Order $record) {
                                return number_format($record->tax_total, 2, '.', '');
                            }),
                    ]),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Support\TextLabel::make('')->label('Total Discount')
                            ->alignLeft()->size('md')->inline()->readOnly(),
                        Support\TextLabel::make('discount_total')->alignRight()->size('md')->inline()
                            ->formatStateUsing(function (Order $record) {
                                return number_format($record->discount_total, 2, '.', '');
                            }),
                    ]),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Support\TextLabel::make('')->label('Discount Code')
                            ->alignLeft()->size('md')->inline()->readOnly(),
                        Support\ButtonAction::make('discount_code')
                            ->execute(function (Closure $get, Closure $set) {
                                return Forms\Components\Actions\Action::make('btn_discount_code')
                                    ->color('secondary')
                                    ->label($get('discount_code'))
                                    ->size('sm')
                                    ->url(DiscountResource::getUrl('edit', ['record' => $get('discount_id') ?? null]));
                            })->disableLabel()->columnSpan(1)->alignRight()->size('sm'),
                    ])
                    ->hidden(function (Order $record) {
                        return is_null($record->discount_code) ? true : false;
                    }),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Support\TextLabel::make('')->label('Grand Total')->alignLeft()->size('md')->color('primary')->inline()->readOnly(),
                        Support\TextLabel::make('total')->alignRight()->size('md')->color('primary')->inline()
                            ->formatStateUsing(function (Order $record) {
                                return number_format($record->total, 2, '.', '');
                            }),
                    ]),
            ])->columnSpan(1);
    }
}
