<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\FilamentTenant\Resources\ServiceOrderResource\Pages\CreateServiceOrder;
use App\FilamentTenant\Resources\ServiceOrderResource\Pages\ViewServiceOrder;
use App\FilamentTenant\Resources\ServiceOrderResource\Pages\ListServiceOrder;
use App\FilamentTenant\Support;
use App\FilamentTenant\Support\BadgeLabel;
use App\FilamentTenant\Support\Divider;
use App\FilamentTenant\Support\SchemaFormBuilder;
use App\FilamentTenant\Support\TextLabel;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Closure;
use Domain\Address\Models\Address;
use Domain\Customer\Models\Customer;
use Domain\Order\Enums\OrderStatuses;
use Domain\Order\Events\AdminOrderStatusUpdatedEvent;
use Domain\Service\Models\Service;
use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Domain\ServiceOrder\Models\ServiceOrder;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Str;
use Filament\Notifications\Notification;

class ServiceOrderResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = ServiceOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    protected static ?string $navigationGroup = 'Service Management';

    public static function form(Form $form): Form
    {

        return $form
            ->columns(3)
            ->schema([
                Forms\Components\Group::make()->schema([
                    Section::make(trans('Customer'))
                        ->schema([
                            Forms\Components\Select::make('customer_id')
                                ->label(trans(''))
                                ->placeholder(trans('Select Customer'))
                                ->required()
                                ->preload()
                                ->optionsFromModel(Customer::class, 'email')
                                ->reactive(),

                            Forms\Components\Group::make()->columns(2)->schema([
                                Placeholder::make('first_name')
                                    ->content(fn (Closure $get) => ($customerId = $get('customer_id'))
                                        ? Customer::whereId($customerId)->first()->first_name
                                        : ''),
                                Placeholder::make('last_name')
                                    ->content(fn (Closure $get) => ($customerId = $get('customer_id'))
                                        ? Customer::whereId($customerId)->first()->last_name
                                        : ''),
                                Placeholder::make('email')
                                    ->content(fn (Closure $get) => ($customerId = $get('customer_id'))
                                        ? Customer::whereId($customerId)->first()->email
                                        : ''),
                                Placeholder::make('mobile')
                                    ->content(fn (Closure $get) => ($customerId = $get('customer_id'))
                                        ? Customer::whereId($customerId)->first()->mobile
                                        : ''),
                            ])->visible(
                                function (array $state) {
                                    return isset($state['customer_id']);
                                }
                            ),

                        ]),
                    Section::make(trans('Service Address'))
                        ->schema([
                            Forms\Components\Select::make('service_address_id')
                                ->label(trans(''))
                                ->placeholder(trans('Select Address'))
                                ->required()
                                ->preload()
                                ->optionsFromModel(
                                    Address::class,
                                    'address_line_1',
                                    fn (Builder $query, Closure $get) => $query->where('customer_id', $get('customer_id'))
                                )
                                ->reactive(),

                            Forms\Components\Group::make()->columns(2)->schema([
                                Placeholder::make('country')
                                    ->content(fn (Closure $get) => ($addressId = $get('service_address_id'))
                                        ? Address::whereId($addressId)->first()->state->country->name
                                        : ''),
                                Placeholder::make('state')
                                    ->content(fn (Closure $get) => ($addressId = $get('service_address_id'))
                                        ? Address::whereId($addressId)->first()->state->name
                                        : ''),
                                Placeholder::make('City/Province')
                                    ->content(fn (Closure $get) => ($addressId = $get('service_address_id'))
                                        ? Address::whereId($addressId)->first()->city
                                        : ''),
                                Placeholder::make('Zip')
                                    ->content(fn (Closure $get) => ($addressId = $get('service_address_id'))
                                        ? Address::whereId($addressId)->first()->zip_code
                                        : ''),
                                Checkbox::make('is_same_as_billing')->reactive()->label('Same as Billing Address'),
                            ])->visible(
                                function (array $state) {
                                    return isset($state['service_address_id']);
                                }
                            ),

                        ])->visible(function (array $state) {
                            return isset($state['customer_id']);
                        }),

                    Section::make(trans('Billing Address'))
                        ->schema([
                            Forms\Components\Select::make('billing_address_id')
                                ->label(trans(''))
                                ->placeholder(trans('Select Address'))
                                ->required()
                                ->preload()
                                ->optionsFromModel(
                                    Address::class,
                                    'address_line_1',
                                    fn (Builder $query, Closure $get) => $query->where('customer_id', $get('customer_id'))
                                )
                                ->reactive(),

                            Forms\Components\Group::make()->columns(2)->schema([
                                Placeholder::make('country')
                                    ->content(fn (Closure $get) => ($addressId = $get('billing_address_id'))
                                        ? Address::whereId($addressId)->first()->state->country->name
                                        : ''),
                                Placeholder::make('state')
                                    ->content(fn (Closure $get) => ($addressId = $get('billing_address_id'))
                                        ? Address::whereId($addressId)->first()->state->name
                                        : ''),
                                Placeholder::make('City/Province')
                                    ->content(fn (Closure $get) => ($addressId = $get('billing_address_id'))
                                        ? Address::whereId($addressId)->first()->city
                                        : ''),
                                Placeholder::make('Zip')
                                    ->content(fn (Closure $get) => ($addressId = $get('billing_address_id'))
                                        ? Address::whereId($addressId)->first()->zip_code
                                        : ''),
                            ])->visible(
                                function (array $state) {
                                    return isset($state['billing_address_id']);
                                }
                            ),

                        ])->visible(function (array $state) {
                            return ! $state['is_same_as_billing'] && isset($state['customer_id']);
                        }),
                    Section::make(trans('Service'))
                        ->schema([
                            Forms\Components\Group::make()->columns(2)->schema([
                                Forms\Components\Select::make('service_id')
                                    ->label(trans('Select Service'))
                                    ->placeholder(trans('Select Service'))
                                    ->required()
                                    ->preload()
                                    ->reactive()
                                    ->optionsFromModel(Service::class, 'name'),

                                DateTimePicker::make('schedule')->minDate(now())->withoutSeconds()->default(now())->timezone(Auth::user()?->timezone),

                                Forms\Components\Group::make()->columnSpan(2)->schema([
                                    Forms\Components\Fieldset::make('')->schema([
                                        Placeholder::make('Service')
                                            ->content(fn (Closure $get) => ($serviceId = $get('service_id'))
                                                ? Service::whereId($serviceId)->first()->name
                                                : ''),
                                        Placeholder::make('Service Price')
                                            ->content(fn (Closure $get) => ($serviceId = $get('service_id'))
                                                ? Service::whereId($serviceId)->first()->selling_price
                                                : ''),
                                        Forms\Components\Group::make()->columnSpan(2)->columns(2)->visible(
                                            fn (Closure $get) => Service::whereId($get('service_id'))->first()?->is_subscription
                                        )->schema([
                                            Placeholder::make('Billing Schedule')
                                                ->content(fn (Closure $get) => ($serviceId = $get('service_id'))
                                                    ? Service::whereId($serviceId)->first()->billing_cycle
                                                    : ''),
                                            Placeholder::make('Due Date')
                                                ->content(fn (Closure $get) => ($serviceId = $get('service_id'))
                                                    ? Service::whereId($serviceId)->first()->due_date_every
                                                    : ''),
                                        ]),

                                    ]),
                                ])
                                    ->visible(
                                        function (array $state) {
                                            return isset($state['service_id']);
                                        }
                                    ),

                                TextLabel::make('')
                                    ->label(trans('Additional Charges'))
                                    ->alignLeft()
                                    ->size('xl')
                                    ->weight('bold')
                                    ->inline()
                                    ->readOnly(),

                                Repeater::make('additional_charges')
                                    ->label('')
                                    ->createItemButtonLabel('Additional Charges')
                                    ->columnSpan(2)
                                    ->defaultItems(0)
                                    ->schema([
                                        TextInput::make('name')->required(),
                                        TextInput::make('quantity')->required()->numeric()->reactive()->default(1),
                                        TextInput::make('price')->required()->numeric()->reactive(),
                                    ])
                                    ->maxItems(3)
                                    ->columns(3),

                            ]),
                        ]),
                    Forms\Components\Section::make('Form Title')
                        ->schema([
                            SchemaFormBuilder::make('data', fn (?Service $record) => $record?->blueprint->schema)
                                ->schemaData(fn (Closure $get) => Service::whereId($get('service_id'))->first()?->blueprint->schema),
                        ])
                        ->hidden(fn (Closure $get) => $get('service_id') === null)
                        ->columnSpan(2),
                ])->columnSpan(2),

                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make('Summary')
                        ->columns(2)
                        ->translateLabel()
                        ->schema([
                            TextLabel::make('')
                                ->label(trans('Service Price'))
                                ->alignLeft()
                                ->size('md')
                                ->inline()
                                ->readOnly(),
                            TextLabel::make('')
                                ->label(fn (Closure $get) => Service::whereId($get('service_id'))->first()?->selling_price ?? 0)
                                ->alignLeft()
                                ->size('md')
                                ->inline()
                                ->readOnly(),
                            TextLabel::make('')
                                ->label(trans('Additional Charges'))
                                ->alignLeft()
                                ->size('md')
                                ->inline()
                                ->readOnly(),
                            TextLabel::make('')
                                ->label(fn (Closure $get) => array_reduce($get('additional_charges'), function ($carry, $data) {
                                    if (isset($data['price']) && is_numeric($data['price']) && isset($data['quantity']) && is_numeric($data['quantity'])) {
                                        return $carry + ($data['price'] * $data['quantity']);
                                    }

                                    return $carry;
                                }, 0))
                                ->alignLeft()
                                ->size('md')
                                ->inline()
                                ->readOnly(),
                            TextLabel::make('')
                                ->label(trans('Total Price'))
                                ->alignLeft()
                                ->size('md')
                                ->inline()
                                ->readOnly()
                                ->color('primary'),
                            TextLabel::make('')
                                ->label(fn (Closure $get) => Service::whereId($get('service_id'))->first()?->selling_price + array_reduce($get('additional_charges'), function ($carry, $data) {
                                    if (isset($data['price']) && is_numeric($data['price']) && isset($data['quantity']) && is_numeric($data['quantity'])) {
                                        return $carry + ($data['price'] * $data['quantity']);
                                    }

                                    return $carry;
                                }, 0))
                                ->alignLeft()
                                ->size('md')
                                ->inline()
                                ->readOnly()
                                ->color('primary'),
                        ]),

                ])->columnSpan(1),

            ]);
    }

    public static function summaryCard(): Section
    {
        return Section::make(trans('Summary'))->schema([
            Forms\Components\Group::make()->columns(2)
                ->schema([
                    BadgeLabel::make(trans('status'))->formatStateUsing(function (string $state): string {
                        return ucfirst($state);
                    })
                        ->color(function ($state) {
                            $newState = str_replace(' ', '_', strtolower($state));

                            return match ($newState) {
                                ServiceOrderStatus::PENDING->value => 'warning',
                                ServiceOrderStatus::CANCELLED->value => 'danger',
                                ServiceOrderStatus::FULFILLED->value => 'success',
                                default => 'secondary',
                            };
                        }),
                    self::summaryEditButton(),
                ]),
            Forms\Components\Group::make()->columns(2)->schema([
                TextLabel::make('')
                    ->label(trans('Created By'))
                    ->alignLeft()
                    ->size('md')
                    ->inline()
                    ->readOnly(),
                TextLabel::make('')
                    ->label(fn ($record) => $record->admin->first_name . ' ' . $record->admin->last_name)
                    ->alignLeft()
                    ->size('md')
                    ->inline()
                    ->readOnly(),
            ]),
            Divider::make(''),
            Forms\Components\Group::make()->columns(2)->schema([
                TextLabel::make('')
                    ->label(trans('Service Price'))
                    ->alignLeft()
                    ->size('md')
                    ->inline()
                    ->readOnly(),
                TextLabel::make('')
                    ->label(fn ($record) => $record->currency_symbol . ' ' . $record->service_price)
                    ->alignLeft()
                    ->size('md')
                    ->inline()
                    ->readOnly(),
                TextLabel::make('')
                    ->label(trans('Additional Charges'))
                    ->alignLeft()
                    ->size('md')
                    ->inline()
                    ->readOnly(),
                TextLabel::make('')
                    ->label(fn ($record) => $record->currency_symbol . ' ' . array_reduce($record->additional_charges, function ($carry, $data) {
                        if (isset($data['price']) && is_numeric($data['price']) && isset($data['quantity']) && is_numeric($data['quantity'])) {
                            return $carry + ($data['price'] * $data['quantity']);
                        }

                        return $carry;
                    }, 0))
                    ->alignLeft()
                    ->size('md')
                    ->inline()
                    ->readOnly(),
                TextLabel::make('')
                    ->label(trans('Total Price'))
                    ->alignLeft()
                    ->size('md')
                    ->inline()
                    ->readOnly()
                    ->color('primary'),
                TextLabel::make('')
                    ->label(fn ($record) => $record->currency_symbol . ' ' . $record->total_price)
                    ->alignLeft()
                    ->size('md')
                    ->inline()
                    ->readOnly()
                    ->color('primary'),
            ]),

        ]);
    }

    private static function summaryEditButton(): Support\ButtonAction
    {
        return Support\ButtonAction::make('Edit')
            ->execute(function (ServiceOrder $record, Closure $get, Closure $set) {
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
                                    ServiceOrderStatus::PENDING->value => trans('Pending'),
                                    ServiceOrderStatus::PROCESSING->value => trans('Processing'),
                                    ServiceOrderStatus::CANCELLED->value => trans('Cancelled'),
                                    ServiceOrderStatus::REFUNDED->value => trans('Refunded'),
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
                            ->dehydrateStateUsing(function (string|null $state) use ($get) {
                                if (filled($state) && $get('send_email') == true) {
                                    return $state;
                                }

                                return null;
                            }),
                    ])
                    ->action(
                        function (array $data, $livewire) use ($record, $set) {

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

                            if ($status == ServiceOrderStatus::CANCELLED->value) {
                                if ( ! in_array($record->status, [ServiceOrderStatus::PENDING, ServiceOrderStatus::FORPAYMENT])) {
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

                            $customer = Customer::where('id', $record->customer_id)->first();

                            if ($customer) {
                                event(new AdminOrderStatusUpdatedEvent(
                                    $customer,
                                    $record,
                                    $shouldSendEmail,
                                    $data['status_options'],
                                    $emailRemarks
                                ));
                            }
                        }
                    );
            })
            ->disableLabel()
            ->columnSpan(1)
            ->alignRight()
            ->size('sm')
            ->hidden(function (ServiceOrder $record) {
                return $record->status == ServiceOrderStatus::CANCELLED ||
                    $record->status == ServiceOrderStatus::FULFILLED;
            });
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
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query
                            ->orderBy('customer_first_name', $direction);
                    })
                    ->formatStateUsing(function ($record) {
                        return Str::limit($record->customer_first_name . ' ' . $record->customer_last_name, 30);
                    })
                    ->searchable(query: function (Builder $query, string $search) {
                        $query->where('customer_first_name', 'like', "%{$search}%")
                            ->orWhere('customer_last_name', 'like', "%{$search}%");
                    })->wrap(),
                Tables\Columns\TextColumn::make('total')
                    ->formatStateUsing(function (ServiceOrder $record) {
                        return $record->currency_symbol . ' ' . number_format((float) $record->total_price, 2, '.', ',');
                    })
                    ->alignRight()
                    ->label(trans('Total'))
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_paid')
                    ->label(trans('Paid'))
                    ->alignRight()
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->sortable()
                    ->label(trans('Order Date'))
                    ->dateTime(timezone: Auth::user()?->timezone),
            ])
            ->filters([])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServiceOrder::route('/'),
            'create' => CreateServiceOrder::route('/create'),
            'view' => ViewServiceOrder::route('/{record}'),
        ];
    }
}
