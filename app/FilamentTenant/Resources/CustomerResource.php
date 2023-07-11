<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources\CustomerResource\RelationManagers\AddressesRelationManager;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Domain\Customer\Actions\DeleteCustomerAction;
use Domain\Customer\Actions\ForceDeleteCustomerAction;
use Domain\Customer\Actions\RestoreCustomerAction;
use Domain\Customer\Enums\Gender;
use Domain\Customer\Enums\Status;
use Domain\Customer\Models\Customer;
use Domain\Tier\Models\Tier;
use Exception;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
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
                        ->required()
                        ->image()
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('first_name')
                        ->translateLabel()
                        ->required()
                        ->string()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('last_name')
                        ->translateLabel()
                        ->required()
                        ->string()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('email')
                        ->translateLabel()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->email()
                        ->rule(Rule::email())
                        ->maxLength(255),
                    Forms\Components\TextInput::make('mobile')
                        ->translateLabel()
                        ->required()
                        ->maxLength(255),
                    Forms\Components\DatePicker::make('birth_date')
                        ->translateLabel()
                        ->required()
                        ->before(fn () => now()),
                    Forms\Components\Select::make('tier_id')
                        ->label(trans('Tier'))
                        ->required()
                        ->preload()
                        ->optionsFromModel(Tier::class, 'name'),
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
                        ->required()
                        ->options(
                            collect(Gender::cases())
                                ->mapWithKeys(fn (Gender $target) => [$target->value => Str::headline($target->value)])
                                ->toArray()
                        )
                        ->in(collect(Gender::cases())->map(fn (Gender $status) => $status->value)->toArray()),
                    Forms\Components\Select::make('status')
                        ->translateLabel()
                        ->required()
                        ->options(
                            collect(Status::cases())
                                ->mapWithKeys(fn (Status $target) => [$target->value => Str::headline($target->value)])
                                ->toArray()
                        )
                        ->in(collect(Status::cases())->map(fn (Status $status) => $status->value)->toArray()),
                ])->columns(2),
            ]);
    }

    /** @throws Exception */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->translateLabel()
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(['first_name', 'last_name'])
                    ->wrap(),
                Tables\Columns\TextColumn::make('email')
                    ->translateLabel()
                    ->searchable()
                    ->sortable(),
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
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(timezone: Filament::auth()->user()?->timezone)
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('tier')
                    ->translateLabel()
                    ->relationship('tier', 'name'),
                Tables\Filters\SelectFilter::make('status')
                    ->options(
                        collect(Status::cases())
                            ->mapWithKeys(fn (Status $target) => [$target->value => Str::headline($target->value)])
                            ->toArray()
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DeleteAction::make()
                        ->using(function (Customer $record) {
                            try {
                                return app(DeleteCustomerAction::class)->execute($record);
                            } catch (DeleteRestrictedException $e) {
                                return false;
                            }
                        }),
                    Tables\Actions\RestoreAction::make()
                        ->using(
                            fn (Customer $record) => DB::transaction(
                                fn () => app(RestoreCustomerAction::class)
                                    ->execute($record)
                            )
                        ),
                    Tables\Actions\ForceDeleteAction::make()
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
                    ->query(fn (Builder $query) => $query->with('tier')->latest())
                    ->mapUsing(
                        ['CUID', 'Email', 'First Name',  'Last Name', 'Mobile', 'Status', 'Birth Date', 'Tier', 'Created At'],
                        fn (Customer $customer): array => [
                            $customer->cuid,
                            $customer->email,
                            $customer->first_name,
                            $customer->last_name,
                            $customer->mobile,
                            $customer->status->value,
                            $customer->birth_date->format(config('tables.date_format')),
                            $customer->tier->name,
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
