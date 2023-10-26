<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\FilamentTenant\Resources\ServiceOrderResource\Pages\CreateServiceOrder;
use App\FilamentTenant\Resources\ServiceOrderResource\Pages\ViewServiceOrder;
use App\FilamentTenant\Resources\ServiceOrderResource\Pages\ListServiceOrder;
use App\FilamentTenant\Resources\ServiceOrderResource\RelationManagers\ServiceBillRelationManager;
use App\FilamentTenant\Resources\ServiceOrderResource\RelationManagers\ServiceTransactionRelationManager;
use App\FilamentTenant\Support\SchemaFormBuilder;
use App\FilamentTenant\Support\TextLabel;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Closure;
use Domain\Address\Models\Address;
use Domain\Currency\Models\Currency;
use Domain\Customer\Models\Customer;
use Domain\Service\Models\Service;
use Domain\ServiceOrder\Actions\CalculateServiceOrderTotalPriceAction;
use Domain\ServiceOrder\Actions\GetTaxableInfoAction;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderAdditionalChargeData;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderTaxData;
use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Domain\ServiceOrder\Models\ServiceOrder;
use Domain\Taxation\Enums\PriceDisplay;
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

class ServiceOrderResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = ServiceOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'Service Management';

    public static function form(Form $form): Form
    {
        $Currency = Currency::whereEnabled(true)->firstOrFail()->symbol;

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
                                        ? Customer::whereId($customerId)->first()?->first_name
                                        : ''),
                                Placeholder::make('last_name')
                                    ->content(fn (Closure $get) => ($customerId = $get('customer_id'))
                                        ? Customer::whereId($customerId)->first()?->last_name
                                        : ''),
                                Placeholder::make('email')
                                    ->content(fn (Closure $get) => ($customerId = $get('customer_id'))
                                        ? Customer::whereId($customerId)->first()?->email
                                        : ''),
                                Placeholder::make('mobile')
                                    ->content(fn (Closure $get) => ($customerId = $get('customer_id'))
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
                                        ? Address::whereId($addressId)->first()?->state->country->name
                                        : ''),
                                Placeholder::make('state')
                                    ->content(fn (Closure $get) => ($addressId = $get('service_address_id'))
                                        ? Address::whereId($addressId)->first()?->state->name
                                        : ''),
                                Placeholder::make('City/Province')
                                    ->content(fn (Closure $get) => ($addressId = $get('service_address_id'))
                                        ? Address::whereId($addressId)->first()?->city
                                        : ''),
                                Placeholder::make('Zip')
                                    ->content(fn (Closure $get) => ($addressId = $get('service_address_id'))
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
                                        ? Address::whereId($addressId)->first()?->state->country->name
                                        : ''),
                                Placeholder::make('state')
                                    ->content(fn (Closure $get) => ($addressId = $get('billing_address_id'))
                                        ? Address::whereId($addressId)->first()?->state->name
                                        : ''),
                                Placeholder::make('City/Province')
                                    ->content(fn (Closure $get) => ($addressId = $get('billing_address_id'))
                                        ? Address::whereId($addressId)->first()?->city
                                        : ''),
                                Placeholder::make('Zip')
                                    ->content(fn (Closure $get) => ($addressId = $get('billing_address_id'))
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
                                        fn (Closure $get) => ! Service::whereId($get('service_id'))->first()?->is_subscription
                                    ),

                                Forms\Components\Group::make()->columnSpan(2)->schema([
                                    Forms\Components\Fieldset::make('')->schema([
                                        Placeholder::make('Service')
                                            ->content(fn (Closure $get) => ($serviceId = $get('service_id'))
                                                ? Service::whereId($serviceId)->first()?->name
                                                : ''),
                                        Placeholder::make('Service Price')
                                            ->content(fn (Closure $get) => ($serviceId = $get('service_id'))
                                                ? $Currency . ' ' . number_format(Service::whereId($serviceId)->first()?->selling_price, 2, '.', ',')
                                                : ''),
                                        Forms\Components\Group::make()->columnSpan(2)->columns(2)->visible(
                                            fn (Closure $get) => Service::whereId($get('service_id'))->first()?->is_subscription
                                        )->schema([
                                            Placeholder::make('Billing Schedule')
                                                ->content(fn (Closure $get) => ($serviceId = $get('service_id'))
                                                    ? Service::whereId($serviceId)->first()?->billing_cycle
                                                    : ''),
                                            Placeholder::make('Due Date')
                                                ->content(fn (Closure $get) => ($serviceId = $get('service_id'))
                                                    ? Service::whereId($serviceId)->first()?->due_date_every
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
                                        TextInput::make('name')->required()->translateLabel(),
                                        TextInput::make('quantity')->required()->numeric()->reactive()->default(1)->translateLabel(),
                                        TextInput::make('price')->required()->reactive()->translateLabel(),
                                    ])
                                    ->maxItems(3)
                                    ->columns(3),

                            ]),
                        ]),
                    Forms\Components\Section::make('Form Title')
                        ->schema([
                            SchemaFormBuilder::make('form', fn (?Service $record) => $record?->blueprint?->schema)
                                ->schemaData(fn (Closure $get) => Service::whereId($get('service_id'))->first()?->blueprint?->schema),
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

                                ->label(fn (Closure $get) => $Currency . ' ' . number_format((Service::whereId($get('service_id'))->first()?->selling_price ?? 0), 2, '.', ','))
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
                                ->label(fn (Closure $get) => $Currency . ' ' . number_format(array_reduce($get('additional_charges'), function ($carry, $data) {
                                    if (isset($data['price']) && is_numeric($data['price']) && isset($data['quantity']) && is_numeric($data['quantity'])) {
                                        return $carry + ($data['price'] * $data['quantity']);
                                    }

                                    return $carry;
                                }, 0), 2, '.', ','))
                                ->alignRight()
                                ->size('md')
                                ->inline()
                                ->readOnly(),
                            Forms\Components\Group::make()->columns(2)->columnSpan(2)->schema([

                                TextLabel::make('')
                                    ->label(fn (Closure $get) => trans('Tax (') .
                                        (self::getTax(
                                            Service::whereId($get('service_id'))->first()?->selling_price ?? 0,
                                            $get('additional_charges'),
                                            $get('is_same_as_billing') ? $get('service_address_id') :
                                                $get('billing_address_id')
                                        )->tax_percentage)  .  '%)')
                                    ->alignLeft()
                                    ->size('md')
                                    ->inline()
                                    ->readOnly(),
                                TextLabel::make('')
                                    ->label(fn ($record, Closure $get) => (self::getTax(
                                        Service::whereId($get('service_id'))->first()?->selling_price ?? 0,
                                        $get('additional_charges'),
                                        $get('is_same_as_billing') ? $get('service_address_id') :
                                            $get('billing_address_id')
                                    )->tax_display) == PriceDisplay::INCLUSIVE ? 'Inclusive' : $Currency . ' ' . (self::getTax(
                                        Service::whereId($get('service_id'))->first()?->selling_price ?? 0,
                                        $get('additional_charges'),
                                        $get('is_same_as_billing') ? $get('service_address_id') :
                                            $get('billing_address_id')
                                    )->tax_total))
                                    ->alignRight()
                                    ->size('md')
                                    ->inline()
                                    ->readOnly(),
                            ])->visible(
                                function (array $state) {
                                    return (isset($state['service_address_id']) && $state['is_same_as_billing']) || isset($state['billing_address_id']);
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
                                ->label(fn (Closure $get) => $Currency . ' ' .  number_format((self::getTax(
                                    Service::whereId($get('service_id'))->first()?->selling_price ?? 0,
                                    $get('additional_charges'),
                                    $get('is_same_as_billing') ? $get('service_address_id') :
                                        $get('billing_address_id')
                                )->total_price), 2, '.', ','))
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

    public static function getSubtotal($selling_price, $additional_charges): float
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
                        $additional_charges ?? []
                    )
                )
            )
            ->getAmount();

        return $subTotal;
    }

    public static function getTax($selling_price, $additional_charges, $billing_address_id)
    {
        $subTotal = self::getSubtotal($selling_price, $additional_charges);

        if ( ! isset($billing_address_id)) {
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
}
