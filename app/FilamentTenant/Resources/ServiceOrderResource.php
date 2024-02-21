<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\FilamentTenant\Resources\ServiceOrderResource\Pages\CreateServiceOrder;
use App\FilamentTenant\Resources\ServiceOrderResource\Pages\ListServiceOrder;
use App\FilamentTenant\Resources\ServiceOrderResource\Pages\ViewServiceOrder;
use App\FilamentTenant\Resources\ServiceOrderResource\RelationManagers\ServiceBillRelationManager;
use App\FilamentTenant\Resources\ServiceOrderResource\RelationManagers\ServiceTransactionRelationManager;
use App\FilamentTenant\Resources\ServiceOrderResource\Rules\PaymentPlanAmountRule;
use App\FilamentTenant\Resources\ServiceOrderResource\Schema;
use App\FilamentTenant\Support\SchemaFormBuilder;
use App\FilamentTenant\Support\TextLabel;
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
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
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
        return $form
            ->columns(3)
            ->schema([

                Forms\Components\Group::make()
                    ->schema([
                        Section::make(trans('Customer'))
                            ->schema([

                                Forms\Components\Select::make('customer')
                                    ->translateLabel()
                                    ->relationship(titleAttribute: 'email')
                                    ->required()
                                    ->preload()
                                    ->searchable()
                                    ->reactive(),

                                Forms\Components\Group::make()
                                    ->columns()
                                    ->visible(fn (Get $get) => $get('customer') !== null)
                                    ->schema([

                                        Placeholder::make('first_name')
                                            ->translateLabel()
                                            ->content(fn (Get $get) => self::customer($get)->first_name),

                                        Placeholder::make('last_name')
                                            ->translateLabel()
                                            ->content(fn (Get $get) => self::customer($get)->last_name),

                                        Placeholder::make('email')
                                            ->translateLabel()
                                            ->content(fn (Get $get) => self::customer($get)->email),

                                        Placeholder::make('mobile')
                                            ->translateLabel()
                                            ->content(fn (Get $get) => self::customer($get)->mobile),
                                    ]),

                            ]),

                        Section::make(trans('Service Address'))
                            ->visible(fn (Get $get) => $get('customer') !== null)
                            ->schema([

                                Forms\Components\Select::make('service_address')
                                    ->label(trans('Select Address'))
                                    ->required()
                                    ->options(
                                        fn (Get $get) => Address::where('customer_id', $get('customer'))
                                            ->pluck('address_line_1', 'id')
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->reactive()
                                    ->dehydrated(false),

                                Forms\Components\Group::make()
                                    ->columns()
                                    ->visible(fn (Get $get) => $get('service_address') !== null)
                                    ->schema([

                                        ...Schema::address('service_address'),

                                        Checkbox::make('is_same_as_billing')
                                            ->label(trans('Same as Billing Address'))
                                            ->reactive()
                                            ->default(true),
                                    ]),

                            ]),

                        Section::make(trans('Billing Address'))
                            ->visible(
                                fn (Get $get) => $get('customer') !== null &&
                                    $get('is_same_as_billing') === false
                            )
                            ->schema([

                                Forms\Components\Select::make('billing_address')
                                    ->label(trans('Select Address'))
                                    ->required()
                                    ->options(
                                        fn (Get $get) => Address::where('customer_id', $get('customer'))
                                            ->pluck('address_line_1', 'id')
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->reactive()
                                    ->dehydrated(false),

                                Forms\Components\Group::make()
                                    ->columns()
                                    ->visible(fn (Get $get) => $get('billing_address') !== null)
                                    ->schema(Schema::address('billing_address')),

                            ]),

                        Section::make(trans('Service'))
                            ->schema([
                                Forms\Components\Group::make()
                                    ->columns(2)
                                    ->schema([
                                        Forms\Components\Select::make('service')
                                            ->translateLabel()
                                            ->relationship(
                                                titleAttribute: 'name',
                                                modifyQueryUsing: fn (Builder $query) => $query->where('status', true)
                                            )
                                            ->columnSpan(2)
                                            ->required()
                                            ->preload()
                                            ->searchable()
                                            ->reactive(),

                                        DateTimePicker::make('schedule')
                                            ->columnSpan(2)
                                            ->minDate(now())
                                            ->seconds(false)
                                            ->default(now())
                                            ->visible(
                                                function (Get $get): bool {
                                                    if ($get('service') === null) {
                                                        return true;
                                                    }

                                                    return ! self::service($get)->is_subscription;
                                                }
                                            ),

                                        Forms\Components\Group::make()
                                            ->columnSpan(2)
                                            ->visible(fn (Get $get) => $get('service') !== null)
                                            ->schema([
                                                Forms\Components\Fieldset::make()
                                                    ->schema([

                                                        Placeholder::make('Service')
                                                            ->content(fn (Get $get) => self::service($get)->name),

                                                        Placeholder::make('Service Price')
                                                            ->content(fn (Get $get) => self::currencyFormat($get, 'servicePrice')),

                                                        Forms\Components\Group::make()
                                                            ->columnSpan(2)
                                                            ->columns()
                                                            ->visible(
                                                                function (Get $get): bool {
                                                                    if ($get('service') === null) {
                                                                        return false;
                                                                    }

                                                                    return self::service($get)->is_subscription;
                                                                }
                                                            )
                                                            ->schema([

                                                                Placeholder::make('Billing Schedule')
                                                                    ->content(
                                                                        fn (Get $get) => ucfirst(self::service($get)->billing_cycle->value ?? '')

                                                                    ),

                                                                Placeholder::make('Due Date every')
                                                                    ->content(
                                                                        fn (Get $get) => trans(':day days after billing date', [
                                                                            'day' => self::ordinalNumber(self::service($get)->due_date_every ?? 0),
                                                                        ])
                                                                    ),
                                                            ]),

                                                    ]),
                                            ]),

                                        Forms\Components\Group::make()
                                            ->schema([
                                                Forms\Components\Fieldset::make()
                                                    ->schema([
                                                        //                                                    TextLabel::make('')
                                                        //                                                        ->label(trans('Payment Plan'))
                                                        //                                                        ->alignLeft()
                                                        //                                                        ->size('xl')
                                                        //                                                        ->weight('bold')
                                                        //                                                        ->inline()
                                                        //                                                        ->readOnly(),

                                                        Radio::make('payment_type')
                                                            ->label(trans('Pay in'))
                                                            ->options(PaymentPlanType::class)
                                                            ->enum(PaymentPlanType::class)
                                                            ->default(PaymentPlanType::FULL)
                                                            ->reactive()
                                                            ->required()
                                                            ->columnSpan(2)
                                                            ->columns(),

                                                        Forms\Components\Select::make('payment_value')
                                                            ->label(trans('Value'))
                                                            ->reactive()
                                                            ->options(PaymentPlanValue::class)
                                                            ->enum(PaymentPlanValue::class)
                                                            ->columnSpan(2)
                                                            ->placeholder(trans('Select Percent / Fixed'))
                                                            ->columns()
                                                            ->visible(fn (Get $get) => $get('payment_type') === PaymentPlanType::MILESTONE),

                                                        Repeater::make('payment_plan')
                                                            ->hiddenLabel()
                                                            ->addActionLabel(trans('Add Milestone'))
                                                            ->columnSpan(2)
                                                            ->defaultItems(1)
                                                            ->reactive()
                                                            ->rule(fn (Get $get) => new PaymentPlanAmountRule(
                                                                floatval(self::currencyFormat($get, 'totalPriceFloat')), $get('payment_value'))
                                                            )
                                                            ->schema([
                                                                TextInput::make('description')
                                                                    ->required()
                                                                    ->translateLabel()
                                                                    ->afterStateUpdated(function ($component, $state, $livewire) {
                                                                        $items = $component->getContainer()->getParentComponent()->getOldState();
                                                                        $livewire->resetErrorBag($component->getStatePath());

                                                                        if (in_array([$component->getName() => $state]['description'], array_column($items, 'description'))) {
                                                                            $livewire->addError($component->getStatePath(), 'duplicated');
                                                                        }
                                                                    }),

                                                                TextInput::make('amount')
                                                                    ->required(),

                                                                Toggle::make('is_generated')
                                                                    ->required()
                                                                    ->translateLabel()
                                                                    ->visible(false)
                                                                    ->default(false),
                                                            ])
                                                            ->columns()
                                                            ->visible(
                                                                fn (Get $get) => $get('payment_type') === PaymentPlanType::MILESTONE
                                                            ),
                                                    ]),

                                            ])
                                            ->columnSpan(2)
                                            ->visible(
                                                fn (Get $get) => $get('service') &&
                                                    ! Service::whereId($get('service'))
                                                        ->first()?->is_subscription
                                            ),

                                        Repeater::make('additional_charges')
                                            ->hiddenLabel()
                                            ->addActionLabel(trans('Additional Charges'))
                                            ->columnSpan(2)
                                            ->defaultItems(0)
                                            ->schema([

                                                TextInput::make('name')
                                                    ->required()
                                                    ->translateLabel(),

                                                TextInput::make('quantity')
                                                    ->translateLabel()
                                                    ->required()
                                                    ->numeric()
                                                    ->reactive()
                                                    ->default(1),

                                                DateTimePicker::make('date')
                                                    ->minDate(now())
                                                    ->seconds(false)
                                                    ->default(now())
                                                    ->disabled()
                                                    ->hidden(),

                                                TextInput::make('price')
                                                    ->translateLabel()
                                                    ->required()
                                                    ->reactive(),
                                            ])
                                            ->columns(3),

                                    ]),
                            ]),

                        Forms\Components\Section::make(trans('Form Title'))
                            ->schema([

                                SchemaFormBuilder::make('form', fn (?Service $record) => $record?->blueprint?->schema)
                                    ->schemaData(fn (Get $get) => Service::whereId($get('service'))->first()?->blueprint?->schema),
                            ])
                            ->hidden(fn (Get $get) => $get('service') === null)
                            ->columnSpan(2),

                    ])
                    ->columnSpan(2),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Summary')
                            ->columns(2)
                            ->translateLabel()
                            ->schema([
                                //                                TextLabel::make('')
                                //                                    ->label(trans('Service Price'))
                                //                                    ->alignLeft()
                                //                                    ->size('md')
                                //                                    ->inline()
                                //                                    ->readOnly(),
                                //                                TextLabel::make('')
                                //                                    ->label(fn (\Filament\Forms\Get $get) => self::currencyFormat($get, 'servicePrice'))
                                //                                    ->alignRight()
                                //                                    ->size('md')
                                //                                    ->inline()
                                //                                    ->readOnly(),
                                //                                TextLabel::make('')
                                //                                    ->label(trans('Additional Charges'))
                                //                                    ->alignLeft()
                                //                                    ->size('md')
                                //                                    ->inline()
                                //                                    ->readOnly(),
                                //                                TextLabel::make('')
                                //                                    ->label(fn (\Filament\Forms\Get $get) => self::currencyFormat($get, 'additionalCharges'))
                                //                                    ->alignRight()
                                //                                    ->size('md')
                                //                                    ->inline()
                                //                                    ->readOnly(),
                                Forms\Components\Group::make()
                                    ->columns()
                                    ->columnSpan(2)
                                    ->schema([

                                        //                                    TextLabel::make('')
                                        //                                        ->label(fn (\Filament\Forms\Get $get) => self::currencyFormat($get, 'taxPercentage'))
                                        //                                        ->alignLeft()
                                        //                                        ->size('md')
                                        //                                        ->inline()
                                        //                                        ->readOnly(),
                                        //                                    TextLabel::make('')
                                        //                                        ->label(fn (\Filament\Forms\Get $get) => self::currencyFormat($get, 'taxTotal'))
                                        //                                        ->alignRight()
                                        //                                        ->size('md')
                                        //                                        ->inline()
                                        //                                        ->readOnly(),
                                    ])
                                    ->visible(fn (array $state) => self::showTax($state)),
                                //                                TextLabel::make('')
                                //                                    ->label(trans('Total Price'))
                                //                                    ->alignLeft()
                                //                                    ->size('md')
                                //                                    ->inline()
                                //                                    ->readOnly()
                                //                                    ->color('primary'),
                                //                                TextLabel::make('')
                                //                                    ->label(fn (\Filament\Forms\Get $get) => self::currencyFormat($get, 'totalPrice'))
                                //                                    ->alignRight()
                                //                                    ->size('md')
                                //                                    ->inline()
                                //                                    ->readOnly()
                                //                                    ->color('primary'),
                            ]),

                    ])
                    ->columnSpan(1),

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
                    ->dateTime(),
            ])
            ->filters([])
            ->actions([])
            ->bulkActions([])
            ->defaultSort('updated_at', 'desc');
    }

    private static function customer(Get $get): Customer
    {
        return once(fn () => Customer::whereKey($get('customer'))->sole());
    }

    private static function service(Get $get): Service
    {
        return once(fn () => Service::whereKey($get('service'))->sole());
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

    private static function currencyFormat(Get $get, string $type): string|float
    {
        $currencySymbol = Currency::whereEnabled(true)->firstOrFail()->symbol;
        $servicePrice = self::service($get)->selling_price ?? 0;
        $additionalCharges = array_reduce($get('additional_charges'), function ($carry, $data) {
            if (isset($data['price']) && is_numeric($data['price']) && isset($data['quantity']) && is_numeric($data['quantity'])) {
                return $carry + ($data['price'] * $data['quantity']);
            }

            return $carry;
        }, 0);

        $taxInfo = (self::getTax(
            $servicePrice,
            $get('additional_charges'),
            (int) ($get('is_same_as_billing') ? $get('service_address') :
                $get('billing_address'))
        ));

        if ($taxInfo->tax_display == PriceDisplay::INCLUSIVE) {
            return PriceDisplay::INCLUSIVE->value;
        }

        $currency = 0.0;

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

        return $currencySymbol.' '.number_format($currency, 2, '.', ',');
    }

    private static function showTax(array $state): bool
    {
        $sellingPrice = Service::whereId($state['service'])->first()?->selling_price ?? 0;

        $taxDisplay = self::getTax(
            $sellingPrice,
            $state['additional_charges'],
            (int) ($state['is_same_as_billing'] ? $state['service_address'] : $state['billing_address'])
        )->tax_display;

        if (isset($taxDisplay)) {
            return true;
        }

        return false;
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
}
