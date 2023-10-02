<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Features\Customer\TierBase;
use App\Features\ECommerce\RewardPoints;
use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources\CustomerResource\RelationManagers\AddressesRelationManager;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Closure;
use Domain\Address\Enums\AddressLabelAs;
use Domain\Address\Models\Country;
use Domain\Address\Models\State;
use Domain\Customer\Actions\DeleteCustomerAction;
use Domain\Customer\Actions\ForceDeleteCustomerAction;
use Domain\Customer\Actions\RestoreCustomerAction;
use Domain\Customer\Actions\SendRegisterInvitationAction;
use Domain\Customer\Enums\Gender;
use Domain\Customer\Enums\RegisterStatus;
use Domain\Customer\Enums\Status;
use Domain\Customer\Models\Customer;
use Domain\RewardPoint\Models\PointEarning;
use Domain\Tier\Enums\TierApprovalStatus;
use Domain\Tier\Models\Tier;
use Exception;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Support\ConstraintsRelationships\Exceptions\DeleteRestrictedException;
use Support\Excel\Actions\ExportBulkAction;
use ErrorException;

class CustomerResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = Customer::class;

    protected static ?string $navigationGroup = 'Customer Management';

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $recordTitleAttribute = 'full_name';

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'email',
            'first_name',
            'last_name',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make([
                    Forms\Components\FileUpload::make('image')
                        ->label(trans('Profile image'))
                        ->mediaLibraryCollection('image')
                        ->nullable()
                        ->image()
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('first_name')
                        ->translateLabel()
                        ->required()
                        ->string()
                        ->rules([
                            function ($record) {
                                return function (string $attribute, mixed $value, Closure $fail) {
                                    if (preg_match('/[^a-zA-Z\s]/', $value)) {
                                        $fail('Input must not contain numerical characters.');
                                    }
                                };
                            },
                        ])
                        ->maxLength(255),
                    Forms\Components\TextInput::make('last_name')
                        ->translateLabel()
                        ->required()
                        ->rules([
                            function ($record) {
                                return function (string $attribute, mixed $value, Closure $fail) {
                                    if (preg_match('/[^a-zA-Z\s]/', $value)) {
                                        $fail('Input must not contain numerical characters.');
                                    }
                                };
                            },
                        ])
                        ->string()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('email')
                        ->label(trans('Email Address'))
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->email()
                        ->rule(Rule::email())
                        ->maxLength(255),
                    Forms\Components\TextInput::make('mobile')
                        ->unique(ignoreRecord: true)
                        ->label(trans('Mobile Number'))
                        ->nullable()
                        ->maxLength(255),
                    Forms\Components\DatePicker::make('birth_date')
                        ->translateLabel()
                        ->nullable()
                        ->before(fn () => now()),
                    Forms\Components\Select::make('tier_id')
                        ->label(trans('Tier'))
                        ->preload()
                        ->hidden(fn () => ! tenancy()->tenant?->features()->active(TierBase::class) ? true : false)
                        ->optionsFromModel(Tier::class, 'name'),

                    Forms\Components\Select::make('tier_approval_status')
                        ->options([
                            TierApprovalStatus::APPROVED->value => 'Approved',
                            TierApprovalStatus::REJECTED->value => 'Rejected',
                        ])
                        ->hidden(function ($record, $context) {
                            /** @var \Domain\Tier\Models\Tier $wholesaler_domestic */
                            $wholesaler_domestic = Tier::whereName(config('domain.tier.wholesaler-domestic'))->first();

                            /** @var \Domain\Tier\Models\Tier $wholesaler_international */
                            $wholesaler_international = Tier::whereName(config('domain.tier.wholesaler-international'))->first();

                            /** @var \Domain\Tier\Models\Tier $tier */
                            $tier = Tier::whereName(config('domain.tier.default'))->first();

                            if ($context === 'create') {
                                return true;
                            }

                            if ($record !== null && ($record->tier_approval_status === TierApprovalStatus::APPROVED || ($tier !== null && $record->tier_id == $tier->getKey()))) {
                                return true;
                            }

                            if ($record !== null && ($record->tier_id === ($wholesaler_domestic->getKey()) && ($wholesaler_domestic->has_approval) == 1)) {
                                return false;
                            }

                            if ($record !== null && ($record->tier_id === ($wholesaler_international->getKey()) && ($wholesaler_international->has_approval) == 1)) {
                                return false;
                            }

                        }),

                    Forms\Components\TextInput::make('password')
                        ->translateLabel()
                        ->password()
                        ->rules(Password::sometimes())
                        ->helperText(
                            app()->environment('local', 'testing')
                                ? trans('Password must be at least 4 characters.')
                                : trans('Password must be at least 8 characters, have 1 special character, 1 number, 1 upper case and 1 lower case.')
                        )
                        ->visible(fn (?Customer $record) => $record === null || ! $record->exists),
                    Forms\Components\TextInput::make('password_confirmation')
                        ->translateLabel()
                        ->password()
                        ->same('password')
                        ->dehydrated(false)
                        ->rules(Password::sometimes())
                        ->visible(fn (?Customer $record) => $record === null || ! $record->exists),
                    Forms\Components\Select::make('gender')
                        ->translateLabel()
                        ->nullable()
                        ->options(
                            collect(Gender::cases())
                                ->mapWithKeys(fn (Gender $target) => [$target->value => Str::headline($target->value)])
                                ->toArray()
                        )
                        ->enum(Gender::class),
                    Forms\Components\Select::make('status')
                        ->translateLabel()
                        ->nullable()
                        ->options(
                            collect(Status::cases())
                                ->mapWithKeys(fn (Status $target) => [$target->value => Str::headline($target->value)])
                                ->toArray()
                        )
                        ->enum(Status::class),
                    Forms\Components\Placeholder::make('earned_points')
                        ->label(trans('Earned points from orders: '))
                        ->content(fn ($record) => PointEarning::whereCustomerId($record?->getKey())->sum('earned_points') ?? 0)
                        ->hidden(fn () => ! tenancy()->tenant?->features()->active(RewardPoints::class) ? true : false),
                ])
                    ->columns(2),
                //                Forms\Components\Fieldset::make(trans('Address'))
                //                    ->schema([
                //                        Forms\Components\Card::make([
                //                            Forms\Components\TextInput::make('shipping_address_line_1')
                //                                ->translateLabel()
                //                                ->required()
                //                                ->string()
                //                                ->maxLength(255)
                //                                ->columnSpanFull(),
                //                            Forms\Components\Select::make('shipping_country_id')
                //                                ->label(trans('Shipping country'))
                //                                ->required()
                //                                ->preload()
                //                                ->optionsFromModel(Country::class, 'name')
                //                                ->reactive()
                //                                ->afterStateUpdated(function (callable $set) {
                //                                    $set('shipping_state_id', null);
                //                                })
                //                                ->dehydrated(false),
                //                            Forms\Components\Select::make('shipping_state_id')
                //                                ->label(trans('Shipping state'))
                //                                ->required()
                //                                ->preload()
                //                                ->optionsFromModel(
                //                                    State::class,
                //                                    'name',
                //                                    fn (Builder $query, callable $get) => $query->where('country_id', $get('shipping_country_id'))
                //                                )
                //                                ->reactive(),
                //                            Forms\Components\TextInput::make('shipping_zip_code')
                //                                ->translateLabel()
                //                                ->required()
                //                                ->string()
                //                                ->maxLength(255)
                //                                ->reactive(),
                //                            Forms\Components\TextInput::make('shipping_city')
                //                                ->translateLabel()
                //                                ->required()
                //                                ->string()
                //                                ->maxLength(255),
                //                            Forms\Components\Select::make('shipping_label_as')
                //                                ->translateLabel()
                //                                ->required()
                //                                ->options(
                //                                    collect(AddressLabelAs::cases())
                //                                        ->mapWithKeys(fn (AddressLabelAs $target) => [
                //                                            $target->value => Str::headline($target->value),
                //                                        ])
                //                                        ->toArray()
                //                                )
                //                                ->enum(AddressLabelAs::class)
                //                                ->columnSpanFull(),
                //                            Forms\Components\Toggle::make('same_as_shipping')
                //                                ->label(trans('set this as billing address as well'))
                //                                ->translateLabel()
                //                                ->reactive(),
                //                        ])
                //                            ->columns(2),
                //                        Forms\Components\Card::make([
                //                            Forms\Components\TextInput::make('billing_address_line_1')
                //                                ->translateLabel()
                //                                ->required()
                //                                ->string()
                //                                ->maxLength(255)
                //                                ->columnSpanFull(),
                //                            Forms\Components\Select::make('billing_country_id')
                //                                ->label(trans('Billing country'))
                //                                ->required()
                //                                ->preload()
                //                                ->optionsFromModel(Country::class, 'name')
                //                                ->reactive()
                //                                ->afterStateUpdated(function (callable $set) {
                //                                    $set('billing_state_id', null);
                //                                })
                //                                ->dehydrated(false),
                //                            Forms\Components\Select::make('billing_state_id')
                //                                ->label(trans('Billing state'))
                //                                ->required()
                //                                ->preload()
                //                                ->optionsFromModel(
                //                                    State::class,
                //                                    'name',
                //                                    fn (Builder $query, callable $get) => $query->where('country_id', $get('billing_country_id'))
                //                                )
                //                                ->reactive(),
                //                            Forms\Components\TextInput::make('billing_zip_code')
                //                                ->translateLabel()
                //                                ->required()
                //                                ->string()
                //                                ->maxLength(255)
                //                                ->reactive(),
                //                            Forms\Components\TextInput::make('billing_city')
                //                                ->translateLabel()
                //                                ->required()
                //                                ->string()
                //                                ->maxLength(255),
                //                            Forms\Components\Select::make('billing_label_as')
                //                                ->translateLabel()
                //                                ->required()
                //                                ->options(
                //                                    collect(AddressLabelAs::cases())
                //                                        ->mapWithKeys(fn (AddressLabelAs $target) => [
                //                                            $target->value => Str::headline($target->value),
                //                                        ])
                //                                        ->toArray()
                //                                )
                //                                ->enum(AddressLabelAs::class)
                //                                ->columnSpanFull(),
                //                        ])
                //                            ->columns(2)
                //                            ->hidden(fn (callable $get) => $get('same_as_shipping')),
                //                    ])
                //                    ->visibleOn('create'),
            ]);
    }

    /** @throws Exception */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')
                    ->collection('image')
                    ->conversion('original')
                    ->circular()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('full_name')
                    ->translateLabel()
                    ->sortable(['first_name', 'last_name'])
                    ->wrap(),
                Tables\Columns\TextColumn::make('email')
                    ->translateLabel()
                    ->sortable(),
                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label(trans('Verified'))
                    ->getStateUsing(fn (Customer $record): bool => $record->hasVerifiedEmail())
                    ->boolean(),
                Tables\Columns\TextColumn::make('mobile')
                    ->translateLabel()
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                Tables\Columns\BadgeColumn::make('tier.name')
                    ->translateLabel()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrap(),
                Tables\Columns\BadgeColumn::make('status')
                    ->translateLabel()
                    ->sortable()
                    ->colors([
                        'success' => Status::ACTIVE->value,
                        'warning' => Status::INACTIVE->value,
                        'danger' => Status::BANNED->value,
                    ]),
                Tables\Columns\BadgeColumn::make('register_status')
                    ->translateLabel()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->colors([
                        'success' => RegisterStatus::REGISTERED->value,
                        'warning' => RegisterStatus::INVITED->value,
                        'danger' => RegisterStatus::UNREGISTERED->value,
                    ]),
                Tables\Columns\TextColumn::make('updated_at')
                    ->translateLabel()
                    ->dateTime(timezone: Filament::auth()->user()?->timezone)
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make()
                    ->translateLabel(),
                Tables\Filters\SelectFilter::make('tier')
                    ->translateLabel()
                    ->relationship('tier', 'name'),
                Tables\Filters\SelectFilter::make('status')
                    ->translateLabel()
                    ->options(
                        collect(Status::cases())
                            ->mapWithKeys(fn (Status $target) => [$target->value => Str::headline($target->value)])
                            ->toArray()
                    ),
                Tables\Filters\SelectFilter::make('email_verified')
                    ->translateLabel()
                    ->options(['1' => 'Verified', '0' => 'Not Verified'])
                    ->query(function (Builder $query, array $data) {
                        $query->when(filled($data['value']), function (Builder $query) use ($data) {
                            /** @var \Domain\Customer\Models\Customer|\Illuminate\Database\Eloquent\Builder $query */
                            match ($data['value']) {
                                '1' => $query->whereNotNull('email_verified_at'),
                                '0' => $query->whereNull('email_verified_at'),
                                default => '',
                            };
                        });
                    }),
                Tables\Filters\SelectFilter::make('register_status')
                    ->translateLabel()
                    ->default(RegisterStatus::REGISTERED->value)
                    ->options(
                        collect(RegisterStatus::cases())
                            ->mapWithKeys(fn (RegisterStatus $target) => [$target->value => Str::headline($target->value)])
                            ->toArray()
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->translateLabel()
                    ->hidden(fn (?Customer $record) => $record?->tier_approval_status == TierApprovalStatus::REJECTED ? true : false),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('send-register-invitation')
                        ->label(fn (Customer $record) => match ($record->register_status) {
                            RegisterStatus::UNREGISTERED => 'Send register invitation',
                            RegisterStatus::INVITED => 'Resend register invitation',
                            RegisterStatus::REJECTED => 'Send rejected email notification',
                            default => throw new ErrorException('Invalid register status.'),
                        })
                        ->translateLabel()
                        ->requiresConfirmation()
                        ->icon('heroicon-o-speakerphone')
                        ->action(function (Customer $record, Tables\Actions\Action $action): void {

                            if($record->register_status == RegisterStatus::UNREGISTERED) {
                                $success = app(SendRegisterInvitationAction::class)
                                    ->execute($record);

                                if ($success) {
                                    $action
                                        ->successNotificationTitle(trans('A registration link has been sent to your email address.'))
                                        ->success();

                                    return;
                                }

                                $action->failureNotificationTitle(trans('Failed to send register invitation.'))
                                    ->failure();
                            }
                        })
                        ->authorize('sendRegisterInvitation')
                        ->withActivityLog(
                            event: 'register-invitation-link-sent',
                            description: fn (Customer $record) => $record->full_name . ' register invitation link sent'
                        )
                        ->visible(fn (Customer $record) => $record->register_status !== RegisterStatus::REGISTERED),
                    Tables\Actions\DeleteAction::make()
                        ->translateLabel()
                        ->using(function (Customer $record) {
                            try {
                                return app(DeleteCustomerAction::class)->execute($record);
                            } catch (DeleteRestrictedException $e) {
                                return false;
                            }
                        })
                        ->hidden(fn (Customer $record) => $record->register_status == RegisterStatus::REJECTED ? false : true),
                    Tables\Actions\RestoreAction::make()
                        ->translateLabel()
                        ->using(
                            fn (Customer $record) => DB::transaction(
                                fn () => app(RestoreCustomerAction::class)
                                    ->execute($record)
                            )
                        ),
                    Tables\Actions\ForceDeleteAction::make()
                        ->translateLabel()
                        ->using(function (Customer $record) {
                            try {
                                return app(ForceDeleteCustomerAction::class)->execute($record);
                            } catch (DeleteRestrictedException $e) {
                                return false;
                            }
                        }),
                ]),
            ])
            ->bulkActions([
                ExportBulkAction::make()
                    ->queue()
                    ->query(
                        fn (Builder $query) => $query
                            ->with('tier')
                            ->latest()
                    )
                    ->mapUsing(
                        ['CUID', 'Email', 'First Name',  'Last Name', 'Mobile', 'Status', 'Birth Date', 'Tier', 'Created At'],
                        fn (Customer $customer): array => [
                            $customer->cuid,
                            $customer->email,
                            $customer->first_name,
                            $customer->last_name,
                            $customer->mobile,
                            $customer->status?->value,
                            $customer->birth_date?->format(config('tables.date_format')),
                            $customer->tier?->name,
                            $customer->created_at?->format(config('tables.date_time_format')),
                        ]
                    ),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            AddressesRelationManager::class,
            ActivitiesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => CustomerResource\Pages\ListCustomers::route('/'),
            'create' => CustomerResource\Pages\CreateCustomer::route('/create'),
            'edit' => CustomerResource\Pages\EditCustomer::route('/{record}/edit'),
        ];
    }

    /** @return Builder<\Domain\Customer\Models\Customer> */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
