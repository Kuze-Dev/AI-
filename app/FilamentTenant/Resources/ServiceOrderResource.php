<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\FilamentTenant\Resources\ServiceOrderResource\Pages\CreateServiceOrder;
use App\FilamentTenant\Resources\ServiceOrderResource\Pages\ViewServiceOrder;
use App\FilamentTenant\Resources\ServiceOrderResource\Pages\ListServiceOrder;
use App\FilamentTenant\Support\BadgeLabel;
use App\FilamentTenant\Support\ButtonAction;
use App\FilamentTenant\Support\Divider;
use App\FilamentTenant\Support\SchemaFormBuilder;
use App\FilamentTenant\Support\TextLabel;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Closure;
use Domain\Address\Models\Address;
use Domain\Customer\Models\Customer;
use Domain\Service\Models\Service;
use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Domain\ServiceOrder\Models\ServiceOrder;
use Filament\Forms;
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
                                Placeholder::make('billing_address')
                                    ->content(fn (Closure $get) => ($customerId = $get('customer_id'))
                                        ? Address::whereCustomerId($customerId)->first()->address_line_1
                                        : ''),
                            ])->visible(
                                function (array $state) {
                                    return isset($state['customer_id']);
                                }
                            ),

                        ]),
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

                                TextInput::make('service_address')->required()->columnSpan(2),

                                Forms\Components\Group::make()->columnSpan(2)->schema([
                                    Forms\Components\Fieldset::make('')->schema([
                                        Placeholder::make('Service')
                                            ->content(fn (Closure $get) => ($serviceId = $get('service_id'))
                                                ? Service::whereId($serviceId)->first()->name
                                                : ''),
                                        Placeholder::make('Service Price')
                                            ->content(fn (Closure $get) => ($serviceId = $get('service_id'))
                                                ? Service::whereId($serviceId)->first()->price
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
                                                    ? Service::whereId($serviceId)->first()->recurring_payment
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
                                ->label(fn (Closure $get) => Service::whereId($get('service_id'))->first()?->price ?? 0)
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
                                ->label(fn (Closure $get) => Service::whereId($get('service_id'))->first()?->price + array_reduce($get('additional_charges'), function ($carry, $data) {
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
                    ->label(fn ($record) => $record->currency_symbol .' '. $record->service_price)
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
                    ->label(fn ($record) => $record->currency_symbol .' '. array_reduce($record->additional_charges, function ($carry, $data) {
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
                    ->label(fn ($record) => $record->currency_symbol .' '. $record->total_price)
                    ->alignLeft()
                    ->size('md')
                    ->inline()
                    ->readOnly()
                    ->color('primary'),
            ]),

        ]);
    }

    private static function summaryEditButton(): ButtonAction
    {
        return ButtonAction::make('Edit')
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
                            ->options(function () {

                                $options = [];
                                foreach(ServiceOrderStatus::cases() as $status) {
                                    $options[] = ucwords(str_replace('_', ' ', $status->value));
                                }
                                // if ($record->is_paid) {
                                //     $options[OrderStatuses::FULFILLED->value] = trans('Fulfilled');
                                // }

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
                    ]);
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
            ->columns([])
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
