<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Features;
use App\Features\Enums\FeatureEnum;
use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\Filament\Resources\TenantResource\Forms\FeatureSelector;
use App\Filament\Resources\TenantResource\Pages;
use App\Filament\Rules\CheckDatabaseConnection;
use App\Filament\Rules\FullyQualifiedDomainNameRule;
// use App\Filament\Support\Forms\FeatureSelector;
use App\FilamentTenant\Support\Divider;
use Domain\Tenant\Models\Tenant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $recordTitleAttribute = 'name';

    #[\Override]
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\TextInput::make('name')
                        ->unique(ignoreRecord: true)
                        ->required(),
                ]),

                Forms\Components\Section::make(trans('Database'))
                    ->collapsed(fn (string $context) => $context === 'edit')
                    ->schema([

                        Forms\Components\TextInput::make(Tenant::internalPrefix().'db_host')
                            ->label(trans('Host'))
                            ->columnSpan(['md' => 3])
                            ->required()
                            ->rule(
                                new CheckDatabaseConnection(config()->string('tenancy.database.template_tenant_connection')),
                                fn (string $context) => $context === 'create'
                            ),

                        Forms\Components\TextInput::make(Tenant::internalPrefix().'db_port')
                            ->label(trans('Port'))
                            ->columnSpan(['md' => 1])
                            ->required(),

                        Forms\Components\TextInput::make(Tenant::internalPrefix().'db_name')
                            ->label(trans('Name'))
                            ->columnSpanFull()
                            ->required(),

                        Forms\Components\TextInput::make(Tenant::internalPrefix().'db_username')
                            ->label(trans('Username'))
                            ->columnSpan(['md' => 2])
                            ->required(),

                        Forms\Components\TextInput::make(Tenant::internalPrefix().'db_password')
                            ->label(trans('Password'))
                            ->columnSpan(['md' => 2])
                            ->password()
                            ->revealable(fn (?Tenant $record) => $record === null)
                            ->required(! app()->isLocal())
                            ->afterStateHydrated(
                                fn (Forms\Components\TextInput $component, ?Tenant $record) => $component
                                    ->state($record ? 'nice try, but we won\'t show the password' : null)),
                    ])
                    ->columns(['md' => 4])
                    ->disabledOn('edit')
                    ->dehydrated(fn (string $context) => $context !== 'edit'),
                Forms\Components\Section::make(trans('Bucket'))
                    ->collapsed(fn (string $context) => $context === 'edit')
                    ->schema([
                        Forms\Components\Select::make(Tenant::internalPrefix().'bucket_driver')
                            ->options([
                                's3' => 'AWS S3 Storage',
                                'r2' => 'Cloudflare R2 Storage',
                                'local' => 'Local',
                            ])
                            ->columnSpanFull()
                            ->reactive()
                            ->default('s3')
                            ->placeholder('Select Storage Driver'),
                        // ->afterStateHydrated(fn (Forms\Components\Select $component, ?Tenant $record) => $component->state($record?->getInternal('driver'))),
                        Forms\Components\TextInput::make(Tenant::internalPrefix().'bucket')
                            ->required(fn (?Tenant $record, Get $get) => ($record === null && in_array($get(Tenant::internalPrefix().'bucket_driver'), ['s3', 'r2'], true)))
                            ->columnSpan(['md' => 4])
                            ->reactive(),
                        // ->afterStateHydrated(fn (Forms\Components\TextInput $component, ?Tenant $record) => $component->state($record?->getInternal('bucket'))),
                        Forms\Components\TextInput::make(Tenant::internalPrefix().'bucket_access_key')
                            ->required(fn (?Tenant $record, Get $get) => ($record === null && in_array($get(Tenant::internalPrefix().'bucket_driver'), ['s3', 'r2'], true)))
                            ->columnSpan(['md' => 2]),
                        // ->afterStateHydrated(fn (Forms\Components\TextInput $component, ?Tenant $record) => $component->state($record?->getInternal('bucket_access_key'))),
                        Forms\Components\TextInput::make(Tenant::internalPrefix().'bucket_secret_key')
                            ->required(fn (?Tenant $record, Get $get) => ($record === null && in_array($get(Tenant::internalPrefix().'bucket_driver'), ['s3', 'r2'], true)))
                            ->columnSpan(['md' => 2]),
                        // ->afterStateHydrated(fn (Forms\Components\TextInput $component, ?Tenant $record) => $component->state($record?->getInternal('bucket_secret_key'))),
                        Divider::make('')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make(Tenant::internalPrefix().'bucket_endpoint')
                            ->required(fn (?Tenant $record, Get $get) => ($record === null && in_array($get(Tenant::internalPrefix().'bucket_driver'), ['s3', 'r2'], true)))
                            ->columnSpan(['md' => 2]),
                        // ->afterStateHydrated(fn (Forms\Components\TextInput $component, ?Tenant $record) => $component->state($record?->getInternal('bucket_endpoint'))),
                        Forms\Components\TextInput::make(Tenant::internalPrefix().'bucket_url')
                            ->hidden(fn (Get $get) => $get(Tenant::internalPrefix().'bucket_driver') === 's3')
                            ->required(fn (?Tenant $record, Get $get) => ($record === null && in_array($get(Tenant::internalPrefix().'bucket_driver'), ['r2'], true)))
                            ->columnSpan(['md' => 2]),
                        // ->afterStateHydrated(fn (Forms\Components\TextInput $component, ?Tenant $record) => $component->state($record?->getInternal('bucket_url'))),
                        Forms\Components\TextInput::make(Tenant::internalPrefix().'bucket_region')
                            ->required(fn (?Tenant $record, Get $get) => ($record === null && in_array($get(Tenant::internalPrefix().'bucket_driver'), ['s3'], true)))
                            ->hidden(fn (Get $get) => $get(Tenant::internalPrefix().'bucket_driver') === 'r2')
                            ->columnSpan(['md' => 2]),
                        // ->afterStateHydrated(fn (Forms\Components\TextInput $component, ?Tenant $record) => $component->state($record?->getInternal('bucket_region'))),
                        Forms\Components\Toggle::make(Tenant::internalPrefix().'bucket_style_endpoint')
                            ->columnSpan(['md' => 2]),
                        // ->afterStateHydrated(fn (Forms\Components\Toggle $component, ?Tenant $record) => $component->state($record?->getInternal('bucket_style_endpoint'))),
                    ])
                    ->columns(['md' => 4])
                    ->disabledOn('edit')
                    ->dehydrated(fn (string $context) => $context !== 'edit'),
                Forms\Components\Section::make(trans('Mail Settings'))
                    ->collapsed(fn (string $context) => $context === 'edit')
                    ->schema([
                        Forms\Components\TextInput::make(Tenant::internalPrefix().'mail_from_address')
                            ->afterStateHydrated(fn (Forms\Components\TextInput $component, ?Tenant $record) => $component->state($record?->getInternal('mail_from_address')))
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make(trans('Domains'))
                    ->collapsed(fn (string $context) => $context === 'edit')
                    ->schema([
                        Forms\Components\Repeater::make('domains')
                            ->relationship()
                            ->reorderable(false)
                            ->minItems(1)
                            ->required()
                            ->simple(
                                Forms\Components\TextInput::make('domain')
                                    ->required()
                                    ->string()
                                    ->distinct()
                                    ->unique(
                                        ignoreRecord: true
                                    )
                                    ->rule(new FullyQualifiedDomainNameRule),
                            ),
                    ]),
                Forms\Components\Section::make(trans('Features'))
                    ->collapsed(fn (string $context) => $context === 'edit')
                    ->dehydrated(false)
                    ->schema([
                        FeatureSelector::make('features')
                            /** @phpstan-ignore argument.type */
                            ->options([
                                new Features\GroupFeature(
                                    base: Features\CMS\CMSBase::class,
                                    extra: [
                                        new Features\GroupFeatureExtra(
                                            extra: [
                                                Features\CMS\Internationalization::class,
                                                Features\CMS\SitesManagement::class,
                                                Features\CMS\GoogleMapField::class,
                                            ],
                                        ),
                                    ]
                                ),
                                new Features\GroupFeature(
                                    base: Features\Customer\CustomerBase::class,
                                    extra: [
                                        new Features\GroupFeatureExtra(
                                            extra: [
                                                Features\Customer\TierBase::class,
                                                Features\Customer\AddressBase::class,
                                            ],
                                        ),
                                    ]
                                ),
                                new Features\GroupFeature(
                                    base: Features\ECommerce\ECommerceBase::class,
                                    extra: [
                                        new Features\GroupFeatureExtra(
                                            extra: [
                                                Features\ECommerce\ColorPallete::class,
                                                Features\ECommerce\ProductBatchUpdate::class,
                                                Features\ECommerce\AllowGuestOrder::class,
                                                Features\ECommerce\RewardPoints::class,
                                            ],
                                        ),
                                    ]
                                ),
                                new Features\GroupFeature(
                                    base: Features\Service\ServiceBase::class,
                                ),
                                new Features\GroupFeature(
                                    base: Features\Shopconfiguration\ShopconfigurationBase::class,
                                    extra: [
                                        new Features\GroupFeatureExtra(
                                            extra: [
                                                Features\Shopconfiguration\TaxZone::class,
                                            ],
                                        ),
                                        new Features\GroupFeatureExtra(
                                            extra: [
                                                Features\Shopconfiguration\PaymentGateway\PaypalGateway::class,
                                                Features\Shopconfiguration\PaymentGateway\StripeGateway::class,
                                                Features\Shopconfiguration\PaymentGateway\OfflineGateway::class,
                                                Features\Shopconfiguration\PaymentGateway\BankTransfer::class,
                                                Features\Shopconfiguration\PaymentGateway\VisionpayGateway::class,
                                            ],
                                            groupLabel: FeatureEnum::PAYMENTS->value,
                                        ),
                                        new Features\GroupFeatureExtra(
                                            extra: [
                                                Features\Shopconfiguration\Shipping\ShippingStorePickup::class,
                                                Features\Shopconfiguration\Shipping\ShippingUsps::class,
                                                Features\Shopconfiguration\Shipping\ShippingUps::class,
                                                Features\Shopconfiguration\Shipping\ShippingAusPost::class,
                                            ],
                                            groupLabel: FeatureEnum::SHIPPING->value,
                                        ),
                                    ]
                                ),
                            ]),
                    ])
                    ->hidden(
                        fn () => ! filament_admin()->can('tenant.updateFeatures')
                    ),
                Forms\Components\Section::make(trans('Google Map Settings'))
                    ->collapsed(fn (string $context) => $context === 'edit')
                    ->schema([
                        Forms\Components\TextInput::make(Tenant::internalPrefix().'google_map_api_key')
                            ->columnSpanFull(),
                    ])->hidden(
                        fn (?Tenant $record) => ! $record?->features()->active(\App\Features\CMS\GoogleMapField::class)
                    ),
                Forms\Components\Section::make(trans('Suspension Option'))
                    ->collapsed(fn (string $context) => $context === 'edit')
                    ->schema([
                        Forms\Components\Toggle::make('is_suspended')
                            ->label('Suspend')
                            ->helpertext('Warning this will suspend the current tenant are you sure with this action?')
                            ->inline(false),
                    ])
                    ->hidden(
                        fn () => ! filament_admin()->can('tenant.canSuspendTenant')
                    ),
            ])->columns(2);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                // Tables\Columns\TextColumn::make('domains.domain')
                //     ->badge()
                //     ->url(
                //         fn (Tenant $record) => $record->domainFirstUrl(),
                //         shouldOpenInNewTab: true
                //     ),
                Tables\Columns\TextColumn::make('total_api_request'),
                Tables\Columns\IconColumn::make('is_suspended')
                    ->label(trans('Active'))
                    ->icons([
                        'heroicon-o-check-circle' => false,
                        'heroicon-o-x-circle' => true,
                    ])
                    ->color(fn (bool $state) => $state ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->translateLabel()
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->translateLabel()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable()
                    ->dateTime(),
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

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTenants::route('/'),
            'create' => Pages\CreateTenant::route('/create'),
            'edit' => Pages\EditTenant::route('/{record}/edit'),
        ];
    }

    #[\Override]
    public static function getRelations(): array
    {
        return [
            ActivitiesRelationManager::class,
        ];
    }
}
