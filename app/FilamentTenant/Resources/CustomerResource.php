<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Features\Customer\TierBase;
use App\Features\ECommerce\RewardPoints;
use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources\CustomerResource\RelationManagers\AddressesRelationManager;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Closure;
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
use ErrorException;
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
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Support\ConstraintsRelationships\Exceptions\DeleteRestrictedException;
use Support\Excel\Actions\ExportBulkAction;

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

                            $tier = Tier::whereId($record?->tier_id)->first();
                            if (! $tier?->has_approval) {
                                return true;
                            }

                            if ($context === 'create') {
                                return true;
                            }

                            if ($record !== null && ($record->tier_approval_status === TierApprovalStatus::APPROVED)) {
                                return true;
                            }

                            return (bool) ($record !== null && $tier->isDefault());
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
                        ->reactive()
                        ->translateLabel()
                        ->nullable()
                        ->options(
                            collect(Status::cases())
                                ->mapWithKeys(fn (Status $target) => [$target->value => Str::headline($target->value)])
                                ->toArray()
                        )
                        ->enum(Status::class),
                    Forms\Components\Select::make('register_status')
                        ->hidden(fn ($record) => $record?->register_status === RegisterStatus::REGISTERED)
                        ->placeholder('Select Register status')
                        ->translateLabel()
                        ->required()
                        ->reactive()
                        ->options(
                            collect(RegisterStatus::cases())
                                ->mapWithKeys(fn (RegisterStatus $target) => [$target->value => Str::headline($target->value)])
                                ->toArray()
                        )
                        ->helperText(function ($state, Closure $set) {
                            if ($state === RegisterStatus::INVITED->value) {
                                $set('status', Status::INACTIVE->value);

                                return 'Inactive status is required when register status is invited.';
                            }
                        })
                        ->enum(RegisterStatus::class),
                    Forms\Components\Placeholder::make('earned_points')
                        ->label(trans('Earned points from orders: '))
                        ->content(fn ($record) => PointEarning::whereCustomerId($record?->getKey())->sum('earned_points') ?? 0)
                        ->hidden(fn () => ! tenancy()->tenant?->features()->active(RewardPoints::class) ? true : false),
                    Forms\Components\Placeholder::make('is_verified')
                        ->label(trans('Is Verified: '))
                        ->content(function ($record) {
                            if ($record?->hasVerifiedEmail()) {
                                return new HtmlString('<span class="px-2 py-1 text-white bg-green-500 rounded-full">Verified</span>');
                            } else {
                                return new HtmlString('<span class="px-2 py-1 text-white bg-red-500 rounded-full">Unverified</span>');
                            }
                        }),
                ])
                    ->columns(2)
                    ->disabled(fn ($record) => $record?->trashed()),
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
                    ->searchable(['first_name', 'last_name'])
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
                    ->hidden(fn () => ! tenancy()->tenant?->features()->active(TierBase::class) ? true : false)
                    ->toggleable(fn () => ! tenancy()->tenant?->features()->active(TierBase::class) ? false : true, isToggledHiddenByDefault: true)
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
                    ->hidden(fn () => ! tenancy()->tenant?->features()->active(TierBase::class) ? true : false)
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
                    ->options([
                        'Registered' => ucfirst(RegisterStatus::REGISTERED->value),
                        'Unregistered' => ucfirst(RegisterStatus::UNREGISTERED->value),
                    ]),
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
                            default => throw new ErrorException('Invalid register status.'),
                        })
                        ->translateLabel()
                        ->requiresConfirmation()
                        ->icon('heroicon-o-speakerphone')
                        ->action(function (Customer $record, Tables\Actions\Action $action): void {

                            if ($record->register_status == RegisterStatus::UNREGISTERED ||
                                $record->register_status == RegisterStatus::INVITED) {
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
                            description: fn (Customer $record) => $record->full_name.' register invitation link sent'
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
                        }),
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
                Tables\Actions\DeleteBulkAction::make()
                    ->authorize('delete'),
                Tables\Actions\ForceDeleteBulkAction::make()
                    ->authorize('forceDelete'),
                Tables\Actions\RestoreBulkAction::make()
                    ->authorize('restore'),
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
