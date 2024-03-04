<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\FilamentTenant\Resources\ServiceOrderResource\Pages\CreateServiceOrder;
use App\FilamentTenant\Resources\ServiceOrderResource\Pages\EditServiceOrder;
use App\FilamentTenant\Resources\ServiceOrderResource\Pages\ListServiceOrder;
use App\FilamentTenant\Resources\ServiceOrderResource\RelationManagers\ServiceBillsRelationManager;
use App\FilamentTenant\Resources\ServiceOrderResource\RelationManagers\ServiceTransactionsRelationManager;
use App\FilamentTenant\Resources\ServiceOrderResource\Rules\PaymentPlanAmountRule;
use App\FilamentTenant\Resources\ServiceOrderResource\Schema;
use App\FilamentTenant\Resources\ServiceOrderResource\Support;
use App\FilamentTenant\Support\SchemaFormBuilder;
use Domain\Address\Models\Address;
use Domain\Service\Models\Service;
use Domain\ServiceOrder\Enums\PaymentPlanType;
use Domain\ServiceOrder\Enums\PaymentPlanValue;
use Domain\ServiceOrder\Enums\ServiceOrderAddressType;
use Domain\ServiceOrder\Models\ServiceOrder;
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

                                                                    return Support::service($get)?->is_subscription;
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
                                                                    $get('payment_value')
                                                                )
                                                            )
                                                            ->columns()
                                                            ->visible(
                                                                fn (Get $get) => $get('payment_type') === PaymentPlanType::MILESTONE
                                                            )
                                                            ->reactive()
                                                            ->schema([
                                                                TextInput::make('description')
                                                                    ->required()
                                                                    ->translateLabel()
                                                                    ->distinct(),

                                                                TextInput::make('amount')
                                                                    ->required(),

                                                                Toggle::make('is_generated')
                                                                    ->required()
                                                                    ->translateLabel()
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->label(trans('Order ID'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('customer_full_name')
                    ->label(trans('Customer'))
                    ->limit(30)
                    ->sortable(['customer_first_name', 'customer_last_name'])
                    ->searchable(['customer_first_name', 'customer_last_name'])
                    ->wrap(),

                Tables\Columns\TextColumn::make('total_price')
                    ->label(trans('Total'))
                    ->money(currency: fn (ServiceOrder $record) => $record->currency_code)
                    ->alignRight()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->translateLabel()
                    ->alignRight()
                    ->alignLeft(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(trans('Order Date'))
                    ->sortable()
                    ->dateTime(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->translateLabel()
                    ->sortable()
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            ServiceTransactionsRelationManager::class,
            ServiceBillsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServiceOrder::route('/'),
            'create' => CreateServiceOrder::route('/create'),
            'edit' => EditServiceOrder::route('/{record}/edit'),
        ];
    }
}
