<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Features;
use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\Filament\Resources\TenantResource\Pages;
use App\Filament\Rules\CheckDatabaseConnection;
use App\Filament\Rules\FullyQualifiedDomainNameRule;
use App\Filament\Support\Forms\FeatureSelector;
use App\FilamentTenant\Support\Divider;
use Closure;
use Domain\Tenant\Models\Tenant;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Unique;
use Stancl\Tenancy\Database\Models\Domain;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static ?string $navigationIcon = 'heroicon-o-office-building';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make([
                    Forms\Components\TextInput::make('name')
                        ->unique(ignoreRecord: true)
                        ->required(),
                ]),
                Forms\Components\Section::make(trans('Database'))
                    ->statePath('database')
                    ->collapsed(fn (string $context) => $context === 'edit')
                    ->schema([
                        Forms\Components\TextInput::make('host')
                            ->required(fn (?Tenant $record) => $record === null)
                            ->columnSpan(['md' => 3])
                            ->afterStateHydrated(fn (Forms\Components\TextInput $component, ?Tenant $record) => $component->state($record?->getInternal('db_host')))
                            ->rule(
                                new CheckDatabaseConnection(config('tenancy.database.template_tenant_connection'), 'data.database'),
                                fn (string $context) => $context === 'create'
                            ),
                        Forms\Components\TextInput::make('port')
                            ->required(fn (?Tenant $record) => $record === null)
                            ->afterStateHydrated(fn (Forms\Components\TextInput $component, ?Tenant $record) => $component->state($record?->getInternal('db_port'))),
                        Forms\Components\TextInput::make('name')
                            ->required(fn (?Tenant $record) => $record === null)
                            ->columnSpanFull()
                            ->afterStateHydrated(fn (Forms\Components\TextInput $component, ?Tenant $record) => $component->state($record?->getInternal('db_name'))),
                        Forms\Components\TextInput::make('username')
                            ->required(fn (?Tenant $record) => $record === null)
                            ->columnSpan(['md' => 2])
                            ->afterStateHydrated(fn (Forms\Components\TextInput $component, ?Tenant $record) => $component->state($record?->getInternal('db_username'))),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->required(fn (?Tenant $record) => $record === null)
                            ->columnSpan(['md' => 2])
                            ->afterStateHydrated(fn (Forms\Components\TextInput $component, ?Tenant $record) => $component->state($record ? 'nice try, but we won\'t show the password' : null)),
                    ])
                    ->columns(['md' => 4])
                    ->disabledOn('edit')
                    ->dehydrated(fn (string $context) => $context !== 'edit'),
                Forms\Components\Section::make(trans('Bucket'))
                    ->statePath('bucket')
                    ->collapsed(fn (string $context) => $context === 'edit')
                    ->schema([
                        Forms\Components\Select::make('driver')
                            ->options([
                                's3' => 'AWS S3 Storage',
                                'r2' => 'Cloudflare R2 Storage',
                            ])
                            ->columnSpanFull()
                            ->reactive()
                            ->default('s3')
                            ->placeholder('Select Storage Driver')
                            ->afterStateHydrated(fn (Forms\Components\Select $component, ?Tenant $record) => $component->state($record?->getInternal('driver'))),
                        Forms\Components\TextInput::make('bucket')
                            ->required(fn (?Tenant $record, Closure $get) => ($record === null && in_array($get('driver'), ['s3', 'r2'])))
                            ->columnSpan(['md' => 4])
                            ->reactive()
                            ->afterStateHydrated(fn (Forms\Components\TextInput $component, ?Tenant $record) => $component->state($record?->getInternal('bucket'))),
                        Forms\Components\TextInput::make('access_key')
                            ->required(fn (?Tenant $record, Closure $get) => ($record === null && in_array($get('driver'), ['s3', 'r2'])))
                            ->columnSpan(['md' => 2])
                            ->afterStateHydrated(fn (Forms\Components\TextInput $component, ?Tenant $record) => $component->state($record?->getInternal('bucket_access_key'))),
                        Forms\Components\TextInput::make('secret_key')
                            ->required(fn (?Tenant $record, Closure $get) => ($record === null && in_array($get('driver'), ['s3', 'r2'])))
                            ->columnSpan(['md' => 2])
                            ->afterStateHydrated(fn (Forms\Components\TextInput $component, ?Tenant $record) => $component->state($record?->getInternal('bucket_secret_key'))),
                        Divider::make('')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('endpoint')
                            ->required(fn (?Tenant $record, Closure $get) => ($record === null && in_array($get('driver'), ['s3', 'r2'])))
                            ->columnSpan(['md' => 2])
                            ->afterStateHydrated(fn (Forms\Components\TextInput $component, ?Tenant $record) => $component->state($record?->getInternal('bucket_endpoint'))),
                        Forms\Components\TextInput::make('url')
                            ->hidden(fn (Closure $get) => $get('driver') == 's3' ? true : false)
                            ->required(fn (?Tenant $record, Closure $get) => ($record === null && in_array($get('driver'), ['r2'])))
                            ->columnSpan(['md' => 2])
                            ->afterStateHydrated(fn (Forms\Components\TextInput $component, ?Tenant $record) => $component->state($record?->getInternal('bucket_url'))),
                        Forms\Components\TextInput::make('region')
                            ->required(fn (?Tenant $record, Closure $get) => ($record === null && in_array($get('driver'), ['s3'])))
                            ->hidden(fn (Closure $get) => $get('driver') == 'r2' ? true : false)
                            ->columnSpan(['md' => 2])
                            ->afterStateHydrated(fn (Forms\Components\TextInput $component, ?Tenant $record) => $component->state($record?->getInternal('bucket_region'))),
                        Forms\Components\Toggle::make('style_endpoint')
                            ->columnSpan(['md' => 2])
                            ->afterStateHydrated(fn (Forms\Components\Toggle $component, ?Tenant $record) => $component->state($record?->getInternal('bucket_style_endpoint'))),
                    ])
                    ->columns(['md' => 4])
                    ->disabledOn('edit')
                    ->dehydrated(fn (string $context) => $context !== 'edit'),
                Forms\Components\Section::make(trans('Mail Settings'))
                    ->statePath('mail')
                    ->collapsed(fn (string $context) => $context === 'edit')
                    ->schema([
                        Forms\Components\TextInput::make('from_address')
                            ->afterStateHydrated(fn (Forms\Components\TextInput $component, ?Tenant $record) => $component->state($record?->getInternal('mail_from_address')))
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make(trans('Domains'))
                    ->collapsed(fn (string $context) => $context === 'edit')
                    ->schema([
                        Forms\Components\Repeater::make('domains')
                            ->afterStateHydrated(function (Forms\Components\Repeater $component, ?Tenant $record, ?array $state) {
                                $component->state($record?->domains->toArray() ?? $state);
                            })
                            ->disableItemMovement()
                            ->minItems(1)
                            ->schema([
                                Forms\Components\TextInput::make('domain')
                                    ->required()
                                    ->unique(
                                        'domains',
                                        callback: fn (?Tenant $record, Unique $rule, ?string $state) => $rule
                                            ->when(
                                                $record?->domains->firstWhere('domain', $state),
                                                fn (Unique $rule, ?Domain $domain) => $rule->ignore($domain)
                                            ),
                                    )
                                    ->rules([new FullyQualifiedDomainNameRule()]),
                            ]),
                    ]),
                Forms\Components\Section::make(trans('Features'))
                    ->collapsed(fn (string $context) => $context === 'edit')
                    ->schema([
                        FeatureSelector::make('features')
                            ->options([
                                Features\CMS\CMSBase::class => [
                                    'label' => trans('CMS'),
                                    'extras' => [
                                        Features\CMS\Internationalization::class => app(Features\CMS\Internationalization::class)->label,
                                        Features\CMS\SitesManagement::class => app(Features\CMS\SitesManagement::class)->label,
                                        Features\CMS\GoogleMapField::class => app(Features\CMS\GoogleMapField::class)->label,

                                    ],
                                ],
                                Features\Customer\CustomerBase::class => [
                                    'label' => trans('Customer'),
                                    'extras' => [
                                        Features\Customer\TierBase::class => app(Features\Customer\TierBase::class)->label,
                                        Features\Customer\AddressBase::class => app(Features\Customer\AddressBase::class)->label,
                                    ],
                                ],
                                Features\ECommerce\ECommerceBase::class => [
                                    'label' => trans('eCommerce'),
                                    'extras' => [
                                        Features\ECommerce\ColorPallete::class => 'Collor Pallete (Color Selector)',
                                        Features\ECommerce\ProductBatchUpdate::class => 'Product Batch Update',
                                        Features\ECommerce\AllowGuestOrder::class => 'Allow Guest Orders',
                                        Features\ECommerce\RewardPoints::class => app(Features\ECommerce\RewardPoints::class)->label,
                                    ],
                                ],
                                Features\Service\ServiceBase::class => [
                                    'label' => trans('Service'),
                                    'extras' => [],
                                ],

                                Features\Shopconfiguration\ShopconfigurationBase::class => [
                                    'label' => trans('Shop Configuration'),
                                    'extras' => [
                                        Features\Shopconfiguration\TaxZone::class => app(Features\Shopconfiguration\TaxZone::class)->label,
                                        'Payments' => [
                                            Features\Shopconfiguration\PaymentGateway\PaypalGateway::class => app(Features\Shopconfiguration\PaymentGateway\PaypalGateway::class)->label,
                                            Features\Shopconfiguration\PaymentGateway\StripeGateway::class => app(Features\Shopconfiguration\PaymentGateway\StripeGateway::class)->label,
                                            Features\Shopconfiguration\PaymentGateway\OfflineGateway::class => app(Features\Shopconfiguration\PaymentGateway\OfflineGateway::class)->label,
                                            Features\Shopconfiguration\PaymentGateway\BankTransfer::class => app(Features\Shopconfiguration\PaymentGateway\BankTransfer::class)->label,
                                            Features\Shopconfiguration\PaymentGateway\VisionpayGateway::class => app(Features\Shopconfiguration\PaymentGateway\VisionpayGateway::class)->label,
                                        ],
                                        'shipping' => [
                                            Features\Shopconfiguration\Shipping\ShippingStorePickup::class => app(Features\Shopconfiguration\Shipping\ShippingStorePickup::class)->label,
                                            Features\Shopconfiguration\Shipping\ShippingUsps::class => app(Features\Shopconfiguration\Shipping\ShippingUsps::class)->label,
                                            Features\Shopconfiguration\Shipping\ShippingUps::class => app(Features\Shopconfiguration\Shipping\ShippingUps::class)->label,
                                            Features\Shopconfiguration\Shipping\ShippingAusPost::class => app(Features\Shopconfiguration\Shipping\ShippingAusPost::class)->label,
                                        ],
                                    ],
                                ],

                            ]),
                    ])->hidden(
                        fn () => ! auth()->user()?->can('tenant.updateFeatures')
                    ),
                Forms\Components\Section::make(trans('Google Map Settings'))
                    ->collapsed(fn (string $context) => $context === 'edit')
                    ->schema([
                        Forms\Components\TextInput::make('google_map_api_key')
                            // ->afterStateHydrated(fn (Forms\Components\TextInput $component, ?Tenant $record) => $component->state($record?->getInternal('google_map_api_key')))
                            ->columnSpanFull(),
                    ])->hidden(
                        fn (?Tenant $record) => ! $record?->features()->active(\App\Features\CMS\GoogleMapField::class)
                    ),
                Forms\Components\Section::make(trans('Cors Setting'))
                    ->collapsed(fn (string $context) => $context === 'edit')
                    ->schema([
                        Forms\Components\TagsInput::make('cors_allowed_origins')
                        ->afterStateHydrated(fn (Forms\Components\TagsInput $component, ?Tenant $record) => $component->state($record?->getInternal('cors_allowed_origins'))),

                    ]),
                Forms\Components\Section::make(trans('Ip White List'))
                    ->collapsed(fn (string $context) => $context === 'edit')
                    ->schema([
                        Forms\Components\Keyvalue::make('ip_white_list')
                            ->keyLabel('Ip Name')
                            ->valueLabel('Ip Address')
                            ->columnSpanFull()
                            ->afterStateHydrated(fn (Forms\Components\Keyvalue $component, ?Tenant $record) => $component->state($record?->getInternal('ip_white_list'))),
                    ]),
                Forms\Components\Section::make(trans('Suspension Option'))
                    ->view('filament.forms.components.redbgheading-section')
                    ->collapsed(fn (string $context) => $context === 'edit')
                    ->schema([
                        Forms\Components\Toggle::make('is_suspended')
                            ->label('Suspend')
                            ->helpertext('Warning this will suspend the current tenant are you sure with this action?')
                            ->inline(false),
                    ])->hidden(fn () => ! auth()->user()?->can('tenant.canSuspendTenant')),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TagsColumn::make('domains.domain'),
                Tables\Columns\TextColumn::make('total_api_request'),
                Tables\Columns\IconColumn::make('is_suspended')
                    ->label(trans('Active'))
                    ->options([
                        'heroicon-o-check-circle' => fn ($state) => $state == false,
                        'heroicon-o-x-circle' => fn ($state) => $state === true,
                    ])
                    ->color(fn ($state) => $state == false ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->sortable(),
            ])
            ->filters([])

            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTenants::route('/'),
            'create' => Pages\CreateTenant::route('/create'),
            'edit' => Pages\EditTenant::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            ActivitiesRelationManager::class,
        ];
    }
}
