<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ServiceOrderResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\ServiceOrderResource;
use App\FilamentTenant\Resources\ServiceOrderResource\Rules\PaymentPlanAmountRule;
use App\FilamentTenant\Resources\ServiceOrderResource\Schema;
use App\FilamentTenant\Resources\ServiceOrderResource\Support;
use App\FilamentTenant\Support\SchemaFormBuilder;
use Domain\Address\Models\Address;
use Domain\Currency\Models\Currency;
use Domain\Service\Models\Service;
use Domain\ServiceOrder\Actions\CalculateServiceOrderTotalPriceAction;
use Domain\ServiceOrder\Actions\GenerateReferenceNumberAction;
use Domain\ServiceOrder\Actions\GetTaxableInfoAction;
use Domain\ServiceOrder\Actions\ServiceOrderCreatedPipelineAction;
use Domain\ServiceOrder\Actions\ServiceOrderMilestoneCreatedPipelineAction;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderAdditionalChargeData;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderCreatedPipelineData;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderTaxData;
use Domain\ServiceOrder\Enums\PaymentPlanType;
use Domain\ServiceOrder\Enums\PaymentPlanValue;
use Domain\ServiceOrder\Enums\ServiceOrderAddressType;
use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Domain\ServiceOrder\Models\ServiceOrder;
use Filament\Actions\Action;
use Filament\Facades\Filament;
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
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property-read \Domain\ServiceOrder\Models\ServiceOrder $record
 */
class CreateServiceOrder extends CreateRecord
{
    use LogsFormActivity;

    protected static string $resource = ServiceOrderResource::class;

    private static bool $is_same_as_billing;

    private static int|string|null $service_address = null;

    private static int|string|null $billing_address = null;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label(trans('filament-panels::resources/pages/create-record.form.actions.create.label'))
                ->action('create')
                ->keyBindings(['mod+s']),
        ];
    }

    #[\Override]
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        /** @var array */
        $rawData = $this->form->getRawState();


        $data['admin_id'] = filament_admin()->getKey();
        $data['reference'] = app(GenerateReferenceNumberAction::class)
            ->execute(ServiceOrder::class);

        $service = Service::whereKey($data['service_id'])->sole();
        $data['schema'] = $service->blueprint?->schema;
        $data['service_name'] = $service->name;
        $data['retail_price'] = $service->retail_price;
        $data['service_price'] = $service->selling_price;
        $data['billing_cycle'] = $service->billing_cycle;
        $data['due_date_every'] = $service->due_date_every;
        $data['pay_upfront'] = $service->pay_upfront;
        $data['is_subscription'] = $service->is_subscription;
        $data['needs_approval'] = $service->needs_approval;
        $data['is_auto_generated_bill'] = $service->is_auto_generated_bill;
        $data['is_partial_payment'] = $service->is_partial_payment;
        $data['status'] = ServiceOrderStatus::fromService($service);

        $currency = Currency::whereEnabled(true)->sole();
        $data['currency_code'] = $currency->code;
        $data['currency_name'] = $currency->name;
        $data['currency_symbol'] = $currency->symbol;

        $taxableInfo = self::getTax(
            $rawData,
            self::getSubTotalPrice(
                $service->selling_price,
                $data['additional_charges'] ?? []
            )
        );
        $data['sub_total'] = $taxableInfo->sub_total;
        $data['tax_display'] = $taxableInfo->tax_display;
        $data['tax_percentage'] = $taxableInfo->tax_percentage;
        $data['tax_total'] = $taxableInfo->tax_total;
        $data['total_price'] = $taxableInfo->total_price;

        $data['schedule'] = $rawData['schedule'];
        // for afterCreate
        self::$is_same_as_billing = $rawData['is_same_as_billing'];
        self::$service_address = $rawData['service_address'];
        self::$billing_address = $rawData['billing_address'];

        return $data;
    }

    public function afterCreate(): void
    {
        // TODO: useless dto
        $dto = new ServiceOrderCreatedPipelineData(
            serviceOrder: $this->record,
            service_address_id: self::$service_address,
            billing_address_id: self::$billing_address,
            is_same_as_billing: self::$is_same_as_billing
        );

        if ($this->record->payment_type === PaymentPlanType::MILESTONE) {
            app(ServiceOrderMilestoneCreatedPipelineAction::class)
                ->execute($dto, createServiceOrderAddress: false);
        } else {
            app(ServiceOrderCreatedPipelineAction::class)
                ->execute($dto, createServiceOrderAddress: false);
        }
    }

    #[\Override]
    public function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([

                Forms\Components\Group::make()
                    ->schema([
                        Section::make(trans('Customer'))
                            ->schema([

                                Forms\Components\Select::make('customer_id')
                                    ->translateLabel()
                                    ->relationship(name: 'customer', titleAttribute: 'email')
                                    ->required()
                                    ->preload()
                                    ->searchable()
                                    ->reactive(),

                                Forms\Components\Group::make()
                                    ->columns()
                                    ->visible(fn (Get $get) => $get('customer_id') !== null)
                                    ->schema([

                                        Placeholder::make('first_name_placeholder')
                                            ->label(trans('First name'))
                                            ->content(fn (Get $get) => Support::customer($get)->first_name),

                                        Placeholder::make('last_name_placeholder')
                                            ->label(trans('Last name'))
                                            ->content(fn (Get $get) => Support::customer($get)->last_name),

                                        Placeholder::make('email_placeholder')
                                            ->label(trans('Email'))
                                            ->content(fn (Get $get) => Support::customer($get)->email),

                                        Placeholder::make('mobile_placeholder')
                                            ->label(trans('Mobile'))
                                            ->content(fn (Get $get) => Support::customer($get)->mobile),

                                        Forms\Components\Hidden::make('customer_first_name')
                                            ->dehydrateStateUsing(fn (Get $get) => Support::customer($get)->first_name),

                                        Forms\Components\Hidden::make('customer_last_name')
                                            ->dehydrateStateUsing(fn (Get $get) => Support::customer($get)->last_name),

                                        Forms\Components\Hidden::make('customer_email')
                                            ->dehydrateStateUsing(fn (Get $get) => Support::customer($get)->email),

                                        Forms\Components\Hidden::make('customer_mobile')
                                            ->dehydrateStateUsing(fn (Get $get) => Support::customer($get)->mobile),
                                    ]),

                            ]),

                        Section::make(trans('Service Address'))
                            ->visible(fn (Get $get) => $get('customer_id') !== null)
                            ->schema([

                                Forms\Components\Select::make('service_address')
                                    ->label(trans('Select Address'))
                                    ->required()
                                    ->options(
                                        fn (Get $get) => Address::where('customer_id', $get('customer_id'))
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

                                        Forms\Components\Fieldset::make()
                                            ->relationship('serviceOrderServiceAddress')
                                            ->schema(Schema::address(
                                                '../service_address',
                                                ServiceOrderAddressType::SERVICE_ADDRESS
                                            )),

                                        Checkbox::make('is_same_as_billing')
                                            ->label(trans('Same as Billing Address'))
                                            ->reactive()
                                            ->default(true)
                                            ->dehydrated(false),
                                    ]),

                            ]),

                        Section::make(trans('Billing Address'))
                            ->visible(
                                fn (Get $get) => $get('customer_id') !== null &&
                                    $get('is_same_as_billing') === false
                            )
                            ->schema([

                                Forms\Components\Select::make('billing_address')
                                    ->label(trans('Select Address'))
                                    ->required()
                                    ->options(
                                        fn (Get $get) => Address::where('customer_id', $get('customer_id'))
                                            ->pluck('address_line_1', 'id')
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->reactive()
                                    ->dehydrated(false),

                                Forms\Components\Group::make()
                                    ->columns()
                                    ->visible(fn (Get $get) => $get('billing_address') !== null)
                                    ->schema([
                                        Forms\Components\Fieldset::make()
                                            ->relationship('serviceOrderBillingAddress')
                                            ->schema(Schema::address(
                                                '../billing_address',
                                                ServiceOrderAddressType::BILLING_ADDRESS
                                            )),
                                    ]),

                            ]),

                        Section::make(trans('Service'))
                            ->schema([
                                Forms\Components\Group::make()
                                    ->columns(2)
                                    ->schema([
                                        Forms\Components\Select::make('service_id')
                                            ->translateLabel()
                                            ->relationship(
                                                name: 'service',
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
                                            ->required()
//                                            ->seconds(false)
                                            ->default(now())
                                            ->visible(
                                                function (Get $get): bool {
                                                    if ($get('service_id') === null) {
                                                        return true;
                                                    }

                                                    return ! Support::service($get)?->is_subscription;
                                                }
                                            ),

                                        Forms\Components\Group::make()
                                            ->columnSpan(2)
                                            ->visible(fn (Get $get) => $get('service_id') !== null)
                                            ->schema([
                                                Forms\Components\Fieldset::make()
                                                    ->schema([

                                                        Placeholder::make('service_placeholder')
                                                            ->label(trans('Service'))
                                                            ->content(fn (Get $get) => Support::service($get)?->name),

                                                        Placeholder::make('service_price_placeholder')
                                                            ->label(trans('Service Price'))
                                                            ->content(fn (Get $get) => Support::currencyFormat($get, 'servicePrice')),

                                                        Forms\Components\Group::make()
                                                            ->columnSpan(2)
                                                            ->columns()
                                                            ->visible(
                                                                function (Get $get): bool {
                                                                    if ($get('service_id') === null) {
                                                                        return false;
                                                                    }

                                                                    return Support::service($get)?->is_subscription ?? false;
                                                                }
                                                            )
                                                            ->schema([

                                                                Placeholder::make('billing_schedule_placeholder')
                                                                    ->label(trans('Billing Schedule'))
                                                                    ->content(
                                                                        fn (Get $get) => ucfirst(Support::service($get)?->billing_cycle->value ?? '')
                                                                    ),

                                                                Placeholder::make('due_date_every_placeholder')
                                                                    ->label(trans('Due Date every'))
                                                                    ->content(
                                                                        fn (Get $get) => trans(':day days after billing date', [
                                                                            'day' => Support::ordinalNumber(Support::service($get)?->due_date_every ?? 0),
                                                                        ])
                                                                    ),
                                                            ]),

                                                    ]),
                                            ]),

                                        Forms\Components\Group::make()
                                            ->schema([
                                                Forms\Components\Fieldset::make(trans('Payment Plan'))
                                                    ->schema([

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
                                                            ->rule(
                                                                fn (Get $get) => new PaymentPlanAmountRule(
                                                                    floatval(Support::currencyFormat($get, 'totalPriceFloat')),
                                                                    PaymentPlanValue::from($get('payment_value'))
                                                                )
                                                            )
                                                            ->columns()
                                                            ->visible(
                                                                fn (Get $get) => $get('payment_value') && $get('payment_type') === PaymentPlanType::MILESTONE
                                                            )
                                                            ->reactive()
                                                            ->schema([
                                                                TextInput::make('description')
                                                                    ->translateLabel()
                                                                    ->required()
                                                                    ->distinct(),

                                                                TextInput::make('amount')
                                                                    ->translateLabel()
                                                                    ->numeric()
                                                                    ->required(),

                                                                Toggle::make('is_generated')
                                                                    ->translateLabel()
                                                                    ->required()
                                                                    ->visible(false)
                                                                    ->default(false),
                                                            ]),
                                                    ]),

                                            ])
                                            ->columnSpan(2)
                                            ->visible(
                                                fn (Get $get) => $get('service_id') &&
                                                    ! Service::whereId($get('service_id'))
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
                            ->visible(fn (Get $get) => $get('service_id') !== null)
                            ->schema([

                                SchemaFormBuilder::make(
                                    'customer_form',
                                    fn (?Service $record) => $record?->blueprint?->schema
                                )
                                    ->schemaData(
                                        fn (Get $get) => Support::service($get)?->blueprint?->schema
                                    ),

                            ])
                            ->columnSpan(2),

                    ])
                    ->columnSpan(2),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Summary')
                            ->columns(2)
                            ->translateLabel()
                            ->schema([

                                Placeholder::make('service_price_placeholder')
                                    ->label(trans('Service Price'))
                                    ->inlineLabel()
                                    ->columnSpanFull()
                                    ->content(fn (Get $get) => Support::currencyFormat($get, 'servicePrice')),

                                Placeholder::make('additional_charges_placeholder')
                                    ->label(trans('Additional Charges'))
                                    ->inlineLabel()
                                    ->columnSpanFull()
                                    ->content(fn (Get $get) => Support::currencyFormat($get, 'additionalCharges')),

                                Forms\Components\Group::make()
                                    ->columns()
                                    ->columnSpan(2)
                                    ->schema([

                                        Placeholder::make('tax_percentage_placeholder')
                                            ->hiddenLabel()
                                            ->content(fn (Get $get) => Support::currencyFormat($get, 'taxPercentage')),

                                        Placeholder::make('tax_total_placeholder')
                                            ->hiddenLabel()
                                            ->content(fn (Get $get) => Support::currencyFormat($get, 'taxTotal')),
                                    ])
                                    ->visible(fn (array $state) => Support::showTax($state)),

                                Placeholder::make('total_price_placeholder')
                                    ->label(trans('Total Price'))
                                    ->inlineLabel()
                                    ->columnSpanFull()
                                    ->content(fn (Get $get) => Support::currencyFormat($get, 'totalPrice')),
                            ]),

                    ])
                    ->columnSpan(1),

            ]);
    }

    private static function getTax(
        array $rawData,
        int|float $subTotalPrice
    ): ServiceOrderTaxData {

        $billingAddressId = $rawData['is_same_as_billing'] === true
            ? $rawData['service_address']
            : $rawData['billing_address'];

        return app(GetTaxableInfoAction::class)
            ->execute(
                $subTotalPrice,
                Address::whereKey($billingAddressId)->sole()
            );
    }

    private static function getSubTotalPrice(float $sellingPrice, array $additionalCharges): int|float
    {
        return app(CalculateServiceOrderTotalPriceAction::class)
            ->execute(
                $sellingPrice,
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
            ->getAmount();
    }
}
