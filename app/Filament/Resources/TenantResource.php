<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Features;
use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\Filament\Resources\TenantResource\Pages;
use App\Filament\Rules\CheckDatabaseConnection;
use App\Filament\Rules\FullyQualifiedDomainNameRule;
use App\Filament\Support\Forms\FeatureSelector;
use Domain\Tenant\Models\Tenant;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

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
                                new CheckDatabaseConnection(config('tenancy.database.template_tenant_connection')),
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
                            ->required()
                            ->afterStateHydrated(
                                fn (Forms\Components\TextInput $component, ?Tenant $record) => $component
                                    ->state($record ? 'nice try, but we won\'t show the password' : null)),
                    ])
                    ->columns(['md' => 4])
                    ->disabledOn('edit')
                    ->dehydrated(fn (string $context) => $context !== 'edit'),

                Forms\Components\Section::make(trans('Domains'))
                    ->collapsed(fn (string $context) => $context === 'edit')
                    ->schema([
                        Forms\Components\Repeater::make('domains')
                            ->relationship()
                            ->reorderable(false)
                            ->minItems(1)
                            ->simple(
                                Forms\Components\TextInput::make('domain')
                                    ->required()
                                    ->string()
                                    ->distinct()
                                    ->unique(
                                        ignoreRecord: true
                                    )
                                    ->rule(new FullyQualifiedDomainNameRule()),
                            ),
                    ]),
                Forms\Components\Section::make(trans('Features'))
                    ->collapsed(fn (string $context) => $context === 'edit')
                    ->dehydrated(false)
                    ->schema([
                        FeatureSelector::make('features')
                            ->options([
                                Features\CMS\CMSBase::class => [
                                    'label' => trans('CMS'),
                                    'extras' => [
                                        Features\CMS\Internationalization::class => app(Features\CMS\Internationalization::class)->label,
                                        Features\CMS\SitesManagement::class => app(Features\CMS\SitesManagement::class)->label,
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
                    ])
                    ->hidden(
                        fn () => ! Filament::auth()
                            ->user()?->can('tenant.updateFeatures')
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
                        fn () => ! Filament::auth()
                            ->user()?->can('tenant.canSuspendTenant')
                    ),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('domains.domain')
                    ->badge(),
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
