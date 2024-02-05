<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\FilamentTenant\Resources\ServiceOrderResource\Pages\CreateServiceOrder;
use App\FilamentTenant\Resources\ServiceOrderResource\Pages\ListServiceOrder;
use App\FilamentTenant\Resources\ServiceOrderResource\Pages\ViewServiceOrder;
use App\FilamentTenant\Resources\ServiceOrderResource\RelationManagers\ServiceBillRelationManager;
use App\FilamentTenant\Resources\ServiceOrderResource\RelationManagers\ServiceTransactionRelationManager;
use App\FilamentTenant\Resources\ServiceOrderResource\Rules\PaymentPlanAmountRule;
use App\FilamentTenant\Support\SchemaFormBuilder;
use App\FilamentTenant\Support\TextLabel;
use Closure;
use Domain\Address\Models\Address;
use Domain\Currency\Models\Currency;
use Domain\Customer\Models\Customer;
use Domain\Service\Models\Service;
use Domain\ServiceOrder\Actions\CalculateServiceOrderTotalPriceAction;
use Domain\ServiceOrder\Actions\GetTaxableInfoAction;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderAdditionalChargeData;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderTaxData;
use Domain\ServiceOrder\Enums\PaymentPlanType;
use Domain\ServiceOrder\Enums\PaymentPlanValue;
use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Domain\ServiceOrder\Models\ServiceOrder;
use Domain\Taxation\Enums\PriceDisplay;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Forms\Components\Toggle;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ServiceOrderResource extends Resource
{
    protected static ?string $model = ServiceOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $recordTitleAttribute = 'reference';

    public static function getNavigationGroup(): ?string
    {
        return trans('Service Management');
    }

    public static function form(Form $form): Form
    {
        $currencySymbol = Currency::whereEnabled(true)->firstOrFail()->symbol;

        return $form
            ->columns(3)
            ->schema([
                Forms\Components\Group::make()->schema([
                    Section::make(trans('Customer'))
                        ->schema([
                            Forms\Components\Select::make('customer_id')
                                ->label(trans('Select Customer'))
                                ->placeholder(trans('Select Customer'))
                                ->required()
                                ->preload()
                                ->optionsFromModel(Customer::class, 'email')
                                ->reactive(),
                            Forms\Components\Group::make()->columns(2)->schema([
                                Placeholder::make('first_name')
                                    ->content(fn (\Filament\Forms\Get $get) => ($customerId = $get('customer_id'))
                                        ? Customer::whereId($customerId)->first()?->first_name
                                        : ''),
                                Placeholder::make('last_name')
                                    ->content(fn (\Filament\Forms\Get $get) => ($customerId = $get('customer_id'))
                                        ? Customer::whereId($customerId)->first()?->last_name
                                        : ''),
                                Placeholder::make('email')
                                    ->content(fn (\Filament\Forms\Get $get) => ($customerId = $get('customer_id'))
                                        ? Customer::whereId($customerId)->first()?->email
                                        : ''),
                                Placeholder::make('mobile')
                                    ->content(fn (\Filament\Forms\Get $get) => ($customerId = $get('customer_id'))
                                        ? Customer::whereId($customerId)->first()?->mobile
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
                                ->label(trans('Select Address'))
                                ->placeholder(trans('Select Address'))
                                ->required()
                                ->preload()
                                ->optionsFromModel(
                                    Address::class,
                                    'address_line_1',
                                    fn (Builder $query, \Filament\Forms\Get $get) => $query->where('customer_id', $get('customer_id'))
                                )
                                ->reactive(),

                            Forms\Components\Group::make()->columns(2)->schema([
                                Placeholder::make('country')
                                    ->content(fn (\Filament\Forms\Get $get) => ($addressId = $get('service_address_id'))
                                        ? Address::whereId($addressId)->first()?->state->country->name
                                        : ''),
                                Placeholder::make('state')
                                    ->content(fn (\Filament\Forms\Get $get) => ($addressId = $get('service_address_id'))
                                        ? Address::whereId($addressId)->first()?->state->name
                                        : ''),
                                Placeholder::make('City/Province')
                                    ->content(fn (\Filament\Forms\Get $get) => ($addressId = $get('service_address_id'))
                                        ? Address::whereId($addressId)->first()?->city
                                        : ''),
                                Placeholder::make('Zip')
                                    ->content(fn (\Filament\Forms\Get $get) => ($addressId = $get('service_address_id'))
                                        ? Address::whereId($addressId)->first()?->zip_code
                                        : ''),
                                Checkbox::make('is_same_as_billing')->reactive()->label('Same as Billing Address')->default(true),
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
                                ->label(trans('Select Address'))
                                ->placeholder(trans('Select Address'))
                                ->required()
                                ->preload()
                                ->optionsFromModel(
                                    Address::class,
                                    'address_line_1',
                                    fn (Builder $query, \Filament\Forms\Get $get) => $query->where('customer_id', $get('customer_id'))
                                )
                                ->reactive(),

                            Forms\Components\Group::make()->columns(2)->schema([
                                Placeholder::make('country')
                                    ->content(fn (\Filament\Forms\Get $get) => ($addressId = $get('billing_address_id'))
                                        ? Address::whereId($addressId)->first()?->state->country->name
                                        : ''),
                                Placeholder::make('state')
                                    ->content(fn (\Filament\Forms\Get $get) => ($addressId = $get('billing_address_id'))
                                        ? Address::whereId($addressId)->first()?->state->name
                                        : ''),
                                Placeholder::make('City/Province')
                                    ->content(fn (\Filament\Forms\Get $get) => ($addressId = $get('billing_address_id'))
                                        ? Address::whereId($addressId)->first()?->city
                                        : ''),
                                Placeholder::make('Zip')
                                    ->content(fn (\Filament\Forms\Get $get) => ($addressId = $get('billing_address_id'))
                                        ? Address::whereId($addressId)->first()?->zip_code
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
                                    ->columnSpan(2)
                                    ->placeholder(trans('Select Service'))
                                    ->required()
                                    ->preload()
                                    ->reactive()
                                    ->optionsFromModel(
                                        Service::class,
                                        'name',
                                        fn (Builder $query) => $query->where('status', true)
                                    ),
                                DateTimePicker::make('schedule')
                                    ->columnSpan(2)
                                    ->minDate(now())
                                    ->withoutSeconds()
                                    ->default(now())
                                    ->timezone(Auth::user()?->timezone)
                                    ->visible(
                                        fn (\Filament\Forms\Get $get) => ! Service::whereId($get('service_id'))->first()?->is_subscription
                                    ),

                                Forms\Components\Group::make()->columnSpan(2)->schema([
                                    Forms\Components\Fieldset::make('')->schema([
                                        Placeholder::make('Service')
                                            ->content(fn (\Filament\Forms\Get $get) => ($serviceId = $get('service_id'))
                                                ? Service::whereId($serviceId)->first()?->name
                                                : ''),
                                        Placeholder::make('Service Price')
                                            ->content(fn (\Filament\Forms\Get $get) => self::currencyFormat($get, 'servicePrice')),
                                        Forms\Components\Group::make()->columnSpan(2)->columns(2)->visible(
                                            fn (\Filament\Forms\Get $get) => Service::whereId($get('service_id'))->first()?->is_subscription
                                        )->schema([
                                            Placeholder::make('Billing Schedule')
                                                ->content(fn (\Filament\Forms\Get $get) => ($serviceId = $get('service_id'))
                                                    ? ucfirst(Service::whereId($serviceId)->first()?->billing_cycle->value ?? '')
                                                    : ''),
                                            Placeholder::make('Due Date every')
                                                ->content(fn (\Filament\Forms\Get $get) => ($serviceId = $get('service_id'))
                                                    ? self::ordinalNumber(Service::whereId($serviceId)->first()?->due_date_every ?? 0).' days after billing date'
                                                    : ''),
                                        ]),

                                    ]),
                                ])

                                    ->visible(
                                        function (array $state) {
                                            return isset($state['service_id']);
                                        }
                                    ),

                                Forms\Components\Group::make()
                                    ->schema([
                                        Forms\Components\Fieldset::make('')->schema([
                                            TextLabel::make('')
                                                ->label(trans('Payment Plan'))
                                                ->alignLeft()
                                                ->size('xl')
                                                ->weight('bold')
                                                ->inline()
                                                ->readOnly(),

                                            Radio::make('payment_type')
                                                ->label(trans('Pay in'))
                                                ->options(
                                                    collect(PaymentPlanType::cases())
                                                        ->mapWithKeys(fn (PaymentPlanType $target) => [$target->value => Str::headline($target->value)])
                                                        ->toArray()
                                                )
                                                ->enum(PaymentPlanType::class)
                                                ->default(PaymentPlanType::FULL->value)
                                                ->reactive()
                                                ->required()
                                                ->columnSpan(2)
                                                ->columns(2),

                                            Forms\Components\Select::make('payment_value')
                                                ->label(trans('Value'))
                                                ->reactive()
                                                ->options(
                                                    collect(PaymentPlanValue::cases())
                                                        ->mapWithKeys(fn (PaymentPlanValue $target) => [$target->value => Str::headline($target->value)])
                                                        ->toArray()
                                                )
                                                ->enum(PaymentPlanValue::class)
                                                ->columnSpan(2)
                                                ->placeholder(trans('Select Percent / Fixed'))
                                                ->columns(2)
                                                ->visible(fn (Closure $get) => $get('payment_type') === 'milestone'),

                                            Repeater::make('payment_plan')
                                                ->label('')
                                                ->createItemButtonLabel('Add Milestone')
                                                ->columnSpan(2)
                                                ->defaultItems(1)
                                                ->reactive()
                                                ->rule(fn (Closure $get) => new PaymentPlanAmountRule(floatval(self::currencyFormat($get, 'totalPriceFloat')), $get('payment_value')))
                                                ->schema([
                                                    TextInput::make('description')->required()->translateLabel()
                                                        ->afterStateUpdated(function ($component, $state, $livewire) {
                                                            $items = $component->getContainer()->getParentComponent()->getOldState();
                                                            $livewire->resetErrorBag($component->getStatePath());

                                                            if (in_array([$component->getName() => $state]['description'], array_column($items, 'description'))) {
                                                                $livewire->addError($component->getStatePath(), 'duplicated');
                                                            }
                                                        }),
                                                    TextInput::make('amount')->required(),
                                                    Toggle::make('is_generated')->required()->translateLabel()->visible(false)->default(false),
                                                ])->columns(2)
                                                ->visible(fn (Closure $get) => $get('payment_value') && $get('payment_type') === 'milestone'),
                                        ]),

                                    ])
                                    ->columnSpan(2)->visible(fn (Closure $get) => $get('service_id') && ! Service::whereId($get('service_id'))->first()?->is_subscription),

                                Repeater::make('additional_charges')
                                    ->label('')
                                    ->createItemButtonLabel('Additional Charges')
                                    ->columnSpan(2)
                                    ->defaultItems(0)
                                    ->schema([
                                        TextInput::make('name')->required()->translateLabel(),
                                        TextInput::make('quantity')->required()->numeric()->reactive()->default(1)->translateLabel(),
                                        DateTimePicker::make('date')
                                            ->minDate(now())
                                            ->withoutSeconds()
                                            ->default(now())
                                            ->disabled()
                                            ->hidden()
                                            ->timezone(Auth::user()?->timezone),
                                        TextInput::make('price')->required()->reactive()->translateLabel(),
                                    ])
                                    ->columns(3),

                            ]),
                        ]),
                    Forms\Components\Section::make('Form Title')
                        ->schema([
                            SchemaFormBuilder::make('form', fn (?Service $record) => $record?->blueprint?->schema)
                                ->schemaData(fn (\Filament\Forms\Get $get) => Service::whereId($get('service_id'))->first()?->blueprint?->schema),
                        ])
                        ->hidden(fn (\Filament\Forms\Get $get) => $get('service_id') === null)
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
                                ->label(fn (\Filament\Forms\Get $get) => self::currencyFormat($get, 'servicePrice'))
                                ->alignRight()
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
                                ->label(fn (\Filament\Forms\Get $get) => self::currencyFormat($get, 'additionalCharges'))
                                ->alignRight()
                                ->size('md')
                                ->inline()
                                ->readOnly(),
                            Forms\Components\Group::make()->columns(2)->columnSpan(2)->schema([

                                TextLabel::make('')
                                    ->label(fn (\Filament\Forms\Get $get) => self::currencyFormat($get, 'taxPercentage'))
                                    ->alignLeft()
                                    ->size('md')
                                    ->inline()
                                    ->readOnly(),
                                TextLabel::make('')
                                    ->label(fn (\Filament\Forms\Get $get) => self::currencyFormat($get, 'taxTotal'))
                                    ->alignRight()
                                    ->size('md')
                                    ->inline()
                                    ->readOnly(),
                            ])->visible(
                                function (array $state) {
                                    return self::showTax($state);
                                }
                            ),
                            TextLabel::make('')
                                ->label(trans('Total Price'))
                                ->alignLeft()
                                ->size('md')
                                ->inline()
                                ->readOnly()
                                ->color('primary'),
                            TextLabel::make('')
                                ->label(fn (\Filament\Forms\Get $get) => self::currencyFormat($get, 'totalPrice'))
                                ->alignRight()
                                ->size('md')
                                ->inline()
                                ->readOnly()
                                ->color('primary'),
                        ]),

                ])->columnSpan(1),

            ]);
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
                        return Str::limit($record->customer_first_name.' '.$record->customer_last_name, 30);
                    })
                    ->searchable(query: function (Builder $query, string $search) {
                        $query->where('customer_first_name', 'like', "%{$search}%")
                            ->orWhere('customer_last_name', 'like', "%{$search}%");
                    })->wrap(),
                Tables\Columns\TextColumn::make('total')
                    ->formatStateUsing(function (ServiceOrder $record) {
                        return $record->currency_symbol.' '.number_format((float) $record->total_price, 2, '.', ',');
                    })
                    ->alignRight()
                    ->label(trans('Total'))
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label(trans('Status'))
                    ->alignRight()
                    ->formatStateUsing(function (string $state): string {
                        if ($state == ServiceOrderStatus::FORPAYMENT->value) {
                            return trans('For Payment');
                        }
                        if ($state == ServiceOrderStatus::INPROGRESS->value) {
                            return trans('In Progress');
                        }

                        return ucfirst($state);
                    })
                    ->color(function ($state) {
                        $newState = str_replace(' ', '_', strtolower($state));

                        return match ($newState) {
                            ServiceOrderStatus::PENDING->value, ServiceOrderStatus::INPROGRESS->value => 'warning',
                            ServiceOrderStatus::CLOSED->value, ServiceOrderStatus::INACTIVE->value, ServiceOrderStatus::CLOSED->value => 'danger',
                            ServiceOrderStatus::COMPLETED->value, ServiceOrderStatus::ACTIVE->value => 'success',
                            default => 'secondary',
                        };
                    })->inline()
                    ->alignLeft(),
                Tables\Columns\TextColumn::make('created_at')
                    ->sortable()
                    ->label(trans('Order Date'))
                    ->dateTime(timezone: Auth::user()?->timezone),
            ])
            ->filters([])
            ->actions([])
            ->bulkActions([])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            ServiceTransactionRelationManager::class,
            ServiceBillRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServiceOrder::route('/'),
            'create' => CreateServiceOrder::route('/create'),
            'view' => ViewServiceOrder::route('/{record}'),
        ];
    }

    private static function ordinalNumber(int $number): string
    {
        if ($number % 100 >= 11 && $number % 100 <= 13) {
            $ordinal = $number.'th';
        } else {
            switch ($number % 10) {
                case 1:
                    $ordinal = $number.'st';
                    break;
                case 2:
                    $ordinal = $number.'nd';
                    break;
                case 3:
                    $ordinal = $number.'rd';
                    break;
                default:
                    $ordinal = $number.'th';
                    break;
            }
        }

        return $ordinal;
    }

    public static function getSubtotal(float $selling_price, array $additionalCharges): float
    {
        $subTotal = app(CalculateServiceOrderTotalPriceAction::class)
            ->execute(
                $selling_price,
                array_filter(
                    array_map(
                        function ($additionalCharge) {
                            if (
                                isset($additionalCharge['price']) &&
                                is_numeric($additionalCharge['price']) &&
                                isset($additionalCharge['quantity']) &&
                                is_numeric($additionalCharge['quantity'])
                            ) {
                                return new ServiceOrderAdditionalChargeData(
                                    (float) $additionalCharge['price'],
                                    (int) $additionalCharge['quantity']
                                );
                            }
                        },
                        $additionalCharges
                    )
                )
            )
            ->getAmount();

        return $subTotal;
    }

    public static function getTax(float $selling_price, array $additionalCharges, ?int $billing_address_id): ServiceOrderTaxData
    {
        $subTotal = self::getSubtotal($selling_price, $additionalCharges);

        if (is_null($billing_address_id) || $billing_address_id === 0) {
            return new ServiceOrderTaxData(
                sub_total: $subTotal,
                tax_display: null,
                tax_percentage: 0,
                tax_total: 0,
                total_price: $subTotal
            );
        }

        $billingAddressData = Address::whereId($billing_address_id)
            ->firstOrFail();

        return app(GetTaxableInfoAction::class)
            ->execute($subTotal, $billingAddressData);
    }

    private static function currencyFormat(Closure $get, string $type): string|float
    {
        $currencySymbol = Currency::whereEnabled(true)->firstOrFail()->symbol;
        $servicePrice = Service::whereId($get('service_id'))->first()?->selling_price ?? 0;
        $additionalCharges = array_reduce($get('additional_charges'), function ($carry, $data) {
            if (isset($data['price']) && is_numeric($data['price']) && isset($data['quantity']) && is_numeric($data['quantity'])) {
                return $carry + ($data['price'] * $data['quantity']);
            }

            return $carry;
        }, 0);

        $taxInfo = (self::getTax(
            $servicePrice,
            $get('additional_charges'),
            (int) ($get('is_same_as_billing') ? $get('service_address_id') :
                $get('billing_address_id'))
        ));

        $currency = 0.0;

        if ($taxInfo->tax_display == PriceDisplay::INCLUSIVE) {
            return PriceDisplay::INCLUSIVE->value;
        }
        if ($type == 'servicePrice') {
            $currency = $servicePrice;
        } elseif ($type == 'additionalCharges') {
            $currency = $additionalCharges;
        } elseif ($type == 'taxPercentage') {
            return 'Tax ('.$taxInfo->tax_percentage.'%)';
        } elseif ($type == 'totalPrice') {
            $currency = $taxInfo->total_price;
        } elseif ($type == 'taxTotal') {
            $currency = $taxInfo->tax_total;
        } elseif ($type == 'totalPriceFloat') {
            return floatval($taxInfo->total_price);
        }

        $formatted = $currencySymbol.' '.number_format($currency, 2, '.', ',');

        return $formatted;
    }

    private static function showTax(array $state): bool
    {
        $sellingPrice = Service::whereId($state['service_id'])->first()?->selling_price ?? 0;

        $taxDisplay = self::getTax(
            $sellingPrice,
            $state['additional_charges'],
            (int) ($state['is_same_as_billing'] ? $state['service_address_id'] : $state['billing_address_id'])
        )->tax_display;

        if (isset($taxDisplay)) {
            return true;
        }

        return false;
    }
}
