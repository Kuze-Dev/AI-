<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Features\Customer\TierBase;
use App\Features\ECommerce\RewardPoints;
use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources\CustomerResource\RelationManagers\AddressesRelationManager;
use App\FilamentTenant\Support\SchemaFormBuilder;
use Closure;
use Domain\Blueprint\Models\Blueprint;
use Domain\Customer\Enums\Gender;
use Domain\Customer\Enums\RegisterStatus;
use Domain\Customer\Enums\Status;
use Domain\Customer\Exports\CustomerExporter;
use Domain\Customer\Models\Customer;
use Domain\RewardPoint\Models\PointEarning;
use Domain\Tenant\TenantFeatureSupport;
use Domain\Tier\Enums\TierApprovalStatus;
use Exception;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $recordTitleAttribute = 'full_name';

    protected static ?int $navigationSort = 2;

    #[\Override]
    public static function getNavigationGroup(): ?string
    {
        return trans('Customer Management');
    }

    #[\Override]
    public static function getGloballySearchableAttributes(): array
    {
        return [
            'email',
            'first_name',
            'last_name',
        ];
    }

    #[\Override]
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\SpatieMediaLibraryFileUpload::make('image')
                        ->label(trans('Profile image'))
                        ->collection('image')
                        ->preserveFilenames()
                        ->nullable()
                        ->image()
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('first_name')
                        ->translateLabel()
                        ->required()
                        ->string()
                        ->rule(
                            fn () => function (string $attribute, mixed $value, Closure $fail) {
                                if (preg_match('/[^\pL\s]/u', $value)) {
                                    $fail('Input must not contain numerical characters.');
                                }
                            },
                        )
                        ->maxLength(255),
                    Forms\Components\TextInput::make('last_name')
                        ->translateLabel()
                        ->required()
                        ->rule(
                            fn () => function (string $attribute, mixed $value, Closure $fail) {
                                if (preg_match('/[^\pL\s]/u', $value)) {
                                    $fail('Input must not contain numerical characters.');
                                }
                            },
                        )
                        ->string()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('email')
                        ->label(trans('Email Address'))
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->email()
                        ->rule(Rule::email())
                        ->maxLength(255),
                    Forms\Components\TextInput::make('username')
                        ->label(trans('Username'))
                        ->required(fn ($state) => ! is_null($state))
                        ->unique(ignoreRecord: true)
                        ->formatStateUsing(fn ($state, Forms\Get $get) => $get('email') == $state ? null : $state)
                        ->rules([
                            function () {
                                return function (string $attribute, mixed $value, Closure $fail) {
                                    if (filter_var($value, FILTER_VALIDATE_EMAIL)) {

                                        $fail('email is not allowed.');
                                    }
                                };
                            },
                        ])
                        ->reactive()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('mobile')
                        ->label(trans('Mobile Number'))
                        ->unique(ignoreRecord: true)
                        ->nullable()
                        ->numeric()
                        ->maxLength(255),
                    Forms\Components\DatePicker::make('birth_date')
                        ->translateLabel()
                        ->nullable()
                        ->before(fn () => null)
                        /**
                         * Important Note: 
                         *  
                         * Base the data on set timezone on config to avoid data
                         * incosistency specially in importing process need to 
                         * set timezone on tenancy to maintain data consistency
                         * in both application and database. 
                        */
                         ->timezone(config('app.timezone')), 
                    Forms\Components\Select::make('tier_id')
                        ->translateLabel()
                        ->hidden(fn () => TenantFeatureSupport::inactive(TierBase::class))
                        ->relationship('tier', 'name')
                        ->searchable()
                        ->preload(),

                    Forms\Components\Select::make('tier_approval_status')
                        ->options(TierApprovalStatus::class)
                        ->enum(TierApprovalStatus::class)
                        ->visibleOn('edit')
                        ->hidden(function (?Customer $record): bool {

                            if ($record === null) {
                                return true;
                            }

                            $tier = $record->tier;

                            if ($tier === null || ! $tier->has_approval) {
                                return true;
                            }

                            if ($record->tier_approval_status === TierApprovalStatus::APPROVED) {
                                return true;
                            }

                            return $tier->isDefault();
                        }),

                    Forms\Components\TextInput::make('password')
                        ->translateLabel()
                        ->password()
                        ->revealable()
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
                        ->revealable()
                        ->same('password')
                        ->dehydrated(false)
                        ->rules(Password::sometimes())
                        ->visible(fn (?Customer $record) => $record === null || ! $record->exists),
                    Forms\Components\Select::make('gender')
                        ->translateLabel()
                        ->nullable()
                        ->options(Gender::class)
                        ->enum(Gender::class),
                    Forms\Components\Select::make('status')
                        ->reactive()
                        ->translateLabel()
                        ->nullable()
                        ->options(Status::class)
                        ->enum(Status::class)
                        ->visibleOn('edit'),
                    Forms\Components\Placeholder::make('earned_points')
                        ->label(trans('Earned points from orders: '))
                        ->content(fn ($record) => PointEarning::whereCustomerId($record?->getKey())->sum('earned_points') ?? 0)
                        ->hidden(fn () => TenantFeatureSupport::inactive(RewardPoints::class)),
                ])
                    ->columns(2)
                    ->disabled(fn ($record) => $record?->trashed()),
                SchemaFormBuilder::make(
                    'data',
                    fn () => Blueprint::where('id', app(\App\Settings\CustomerSettings::class)->blueprint_id)->first()?->schema
                )->hidden(
                    fn () => is_null(app(\App\Settings\CustomerSettings::class)->blueprint_id)
                ),
            ]);
    }

    /** @throws Exception */
    #[\Override]
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
                    ->searchable()
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
                Tables\Columns\TextColumn::make('tier.name')
                    ->translateLabel()
                    ->badge()
                    ->sortable()
                    ->hidden(fn () => TenantFeatureSupport::inactive(TierBase::class))
                    ->toggleable(fn () => (bool) TenantFeatureSupport::active(TierBase::class),
                        isToggledHiddenByDefault: true
                    )
                    ->wrap(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->translateLabel()
                    ->sortable()
                    ->colors([
                        'success' => Status::ACTIVE->value,
                        'warning' => Status::INACTIVE->value,
                        'danger' => Status::BANNED->value,
                    ]),
                Tables\Columns\TextColumn::make('register_status')
                    ->translateLabel()
                    ->badge()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->colors([
                        'success' => RegisterStatus::REGISTERED->value,
                        'warning' => RegisterStatus::INVITED->value,
                        'danger' => RegisterStatus::UNREGISTERED->value,
                    ]),
                Tables\Columns\TextColumn::make('updated_at')
                    ->translateLabel()
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->translateLabel()
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make()
                    ->translateLabel(),
                Tables\Filters\SelectFilter::make('tier')
                    ->hidden(fn () => TenantFeatureSupport::inactive(TierBase::class))
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
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->translateLabel()
                    ->hidden(fn (?Customer $record) => $record?->tier_approval_status === TierApprovalStatus::REJECTED),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DeleteAction::make()
                        ->translateLabel(),
                    Tables\Actions\RestoreAction::make()
                        ->translateLabel(),
                    Tables\Actions\ForceDeleteAction::make()
                        ->translateLabel(),
                ]),
            ])
            ->bulkActions([
                // TODO: export only RegisterStatus::REGISTERED
                Tables\Actions\ExportBulkAction::make()
                    ->exporter(CustomerExporter::class)
//                ->authorize() // TODO: authorize customer export
                    ->withActivityLog(
                        event: 'bulk-exported',
                        description: fn (Tables\Actions\ExportBulkAction $action) => 'Bulk Exported '.$action->getModelLabel(),
                        properties: fn (Tables\Actions\ExportBulkAction $action) => [
                            'selected_record_ids' => $action->getRecords()
                                ?->map(
                                    function (int|string|Customer $model): Customer {
                                        if ($model instanceof Customer) {
                                            return $model;
                                        }

                                        return Customer::whereKey($model)->first();
                                    }
                                ),
                        ]
                    ),
                Tables\Actions\DeleteBulkAction::make()
                    ->authorize('delete'),
                Tables\Actions\ForceDeleteBulkAction::make()
                    ->authorize('forceDelete'),
                Tables\Actions\RestoreBulkAction::make()
                    ->authorize('restore'),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    #[\Override]
    public static function getRelations(): array
    {
        return [
            AddressesRelationManager::class,
            ActivitiesRelationManager::class,
        ];
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => CustomerResource\Pages\ListCustomers::route('/'),
            'create' => CustomerResource\Pages\CreateCustomer::route('/create'),
            'edit' => CustomerResource\Pages\EditCustomer::route('/{record}/edit'),
        ];
    }

    /** @return Builder<\Domain\Customer\Models\Customer> */
    #[\Override]
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->where('register_status', RegisterStatus::REGISTERED);
    }

    #[\Override]
    public static function canCreate(): bool
    {
        return false;
    }
}
