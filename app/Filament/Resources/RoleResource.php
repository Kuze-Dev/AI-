<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\Filament\Resources\RoleResource\Pages;
use App\Filament\Resources\RoleResource\Support\PermissionGroup;
use App\Filament\Resources\RoleResource\Support\PermissionGroupCollection;
use Closure;
use Domain\Role\Actions\DeleteRoleAction;
use Domain\Role\Exceptions\CantDeleteRoleWithAssociatedUsersException;
use Domain\Role\Models\Role;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Support\ConstraintsRelationships\Exceptions\DeleteRestrictedException;

class RoleResource extends Resource
{
    public static PermissionGroupCollection $permissionGroups;

    protected static ?string $model = Role::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationGroup = 'Access';

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static string|array $$routeMiddleware = ['password.confirm:filament.auth.password.confirm'];

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
                        ->required()
                        ->string()
                        ->maxLength(255),
                    Forms\Components\Select::make('guard_name')
                        ->default(config('auth.defaults.guard'))
                        ->options(self::getGuards()->mapWithKeys(fn (string $guardName) => [$guardName => $guardName]))
                        ->required()
                        ->reactive(),
                ])
                    ->columns(['lg' => 2]),

                Forms\Components\Section::make('Permissions')
                    ->schema(self::generatePermissionGroupsFormSchema(...)),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->formatStateUsing(fn ($state): string => Str::headline($state))
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('guard_name'),
                Tables\Columns\BadgeColumn::make('permissions_count')
                    ->counts('permissions')
                    ->colors(['success']),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('guard_name')
                    ->options(self::getGuards()->mapWithKeys(fn (string $guardName) => [$guardName => $guardName])),
            ])

            ->actions([
                Tables\Actions\EditAction::make()
                    ->authorize('update'),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DeleteAction::make()
                        ->using(function (Role $record) {
                            try {
                                return app(DeleteRoleAction::class)->execute($record);
                            } catch (\Exception $e) {

                                if ($e instanceof DeleteRestrictedException) {

                                    Notification::make()
                                        ->danger()
                                        ->title('Delete of this Record is Restricted')
                                        ->body($e->getMessage())
                                        ->send();

                                    return $e->getMessage();
                                }

                                if ($e instanceof CantDeleteRoleWithAssociatedUsersException) {

                                    Notification::make()
                                        ->danger()
                                        ->title('Cannot Delete this Record')
                                        ->body('Cannot Delete Role with Associated Users!')
                                        ->send();

                                    return false;

                                }

                                return false;
                            }
                        })
                        ->authorize('delete'),
                ]),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }

    /** @return Collection<int, string> */
    private static function getGuards(): Collection
    {
        return (new Collection(config('auth.guards')))
            ->reject(fn (array $config, string $guard) => $guard === 'sanctum')
            ->map(fn (array $config, string $guard) => $guard);
    }

    /** @return array<\Filament\Forms\Components\Component> */
    private static function generatePermissionGroupsFormSchema(Closure $get): array
    {
        $guard = $get('guard_name');
        self::$permissionGroups = PermissionGroupCollection::make(['guard_name' => $guard]);

        if (self::$permissionGroups->isEmpty()) {
            return [
                Forms\Components\View::make('filament.roles.empty-permissions')
                    ->viewData(['guard' => $guard]),
            ];
        }

        return [
            Forms\Components\Hidden::make('permissions')
                ->reactive()
                ->formatStateUsing(fn (?Role $record) => $record ? $record->permissions->pluck('id') : [])
                ->dehydrateStateUsing(function (\Filament\Forms\Get $get): array {
                    return self::$permissionGroups->reduce(
                        function (array $permissions, PermissionGroup $permissionGroup, string $groupName) use ($get): array {
                            if ($get($groupName) ?? false) {
                                array_push($permissions, $permissionGroup->main->id);
                            } else {
                                $permissions = array_merge($permissions, $get("{$groupName}_abilities") ?? []);
                            }

                            return $permissions;
                        },
                        []
                    );
                }),
            Forms\Components\Toggle::make('select_all')
                ->onIcon('heroicon-s-shield-check')
                ->offIcon('heroicon-s-shield-exclamation')
                ->helperText(trans('Enable all Permissions for this role'))
                ->reactive()
                ->formatStateUsing(fn (?Role $record) => self::$permissionGroups->every(fn (PermissionGroup $permissionGroup): bool => $record?->hasPermissionTo($permissionGroup->main) ?? false))
                ->afterStateUpdated(function (\Filament\Forms\Get $get, \Filament\Forms\Set $set, bool $state): void {
                    self::$permissionGroups->each(function (PermissionGroup $permissionGroup, string $groupName) use ($get, $set, $state): void {
                        $set($groupName, $state);

                        self::refreshPermissionGroupAbilitiesState($groupName, $permissionGroup, $get, $set);
                    });
                })
                ->dehydrated(false),
            Forms\Components\Grid::make(['sm' => 2])
                ->schema(
                    self::$permissionGroups->map(
                        fn (PermissionGroup $permissionGroup, string $groupName) => Forms\Components\Card::make()
                            ->schema([
                                Forms\Components\Toggle::make($groupName)
                                    ->label(Str::headline($groupName))
                                    ->helperText('Enable all abilities for this resource')
                                    ->onIcon('heroicon-s-lock-open')
                                    ->offIcon('heroicon-s-lock-closed')
                                    ->reactive()
                                    ->formatStateUsing(fn (?Role $record) => $record?->hasPermissionTo($permissionGroup->main))
                                    ->afterStateUpdated(function (\Filament\Forms\Get $get, \Filament\Forms\Set $set) use ($groupName, $permissionGroup): void {
                                        self::refreshPermissionGroupAbilitiesState($groupName, $permissionGroup, $get, $set);
                                        self::refreshSelectAllState($get, $set);
                                    })
                                    ->dehydrated(false),
                                Forms\Components\Fieldset::make('Abilities')
                                    ->schema([
                                        Forms\Components\CheckboxList::make("{$groupName}_abilities")
                                            ->disableLabel()
                                            ->options($permissionGroup->abilities->mapWithKeys(fn (Permission $permission) => [
                                                $permission->id => Str::headline(explode('.', $permission->name, 2)[1]),
                                            ]))
                                            ->columns(2)
                                            ->reactive()
                                            ->formatStateUsing(function (Forms\Components\CheckboxList $component, ?Role $record) use ($permissionGroup): array {
                                                if (! $record) {
                                                    return [];
                                                }

                                                if ($record->hasPermissionTo($permissionGroup->main)) {
                                                    return array_keys($component->getOptions());
                                                }

                                                return $record->permissions->pluck('id')
                                                    ->intersect(array_keys($component->getOptions()))
                                                    ->values()
                                                    ->toArray();
                                            })
                                            ->afterStateUpdated(function (\Filament\Forms\Get $get, \Filament\Forms\Set $set) use ($groupName, $permissionGroup): void {
                                                self::refreshPermissionGroupState($groupName, $permissionGroup, $get, $set);
                                                self::refreshSelectAllState($get, $set);
                                            })
                                            ->dehydrated(false),
                                    ])
                                    ->columns(1),
                            ])->hidden(fn () => self::hideFeaturePermission($permissionGroup->main->name))
                            ->columnSpan(1)
                    )
                        ->toArray()
                ),
        ];
    }

    private static function hideFeaturePermission(string $groupName): bool
    {

        /** @var bool */
        return match ($groupName) {
            'site' => tenancy()->tenant?->features()->inactive(\App\Features\CMS\SitesManagement::class),
            'country' => tenancy()->tenant?->features()->inactive(\App\Features\ECommerce\ECommerceBase::class),
            'currency' => tenancy()->tenant?->features()->inactive(\App\Features\ECommerce\ECommerceBase::class),
            'discount' => tenancy()->tenant?->features()->inactive(\App\Features\ECommerce\ECommerceBase::class),
            'order' => tenancy()->tenant?->features()->inactive(\App\Features\ECommerce\ECommerceBase::class),
            'product' => tenancy()->tenant?->features()->inactive(\App\Features\ECommerce\ECommerceBase::class),
            'paymentMethod' => tenancy()->tenant?->features()->inactive(\App\Features\ECommerce\ECommerceBase::class),
            'shippingMethod' => tenancy()->tenant?->features()->inactive(\App\Features\ECommerce\ECommerceBase::class),
            'taxZone' => tenancy()->tenant?->features()->inactive(\App\Features\ECommerce\ECommerceBase::class),
            'ecommerceSettings' => tenancy()->tenant?->features()->inactive(\App\Features\ECommerce\ECommerceBase::class),
            'customers' => tenancy()->tenant?->features()->inactive(\App\Features\Customer\CustomerBase::class),
            'tier' => tenancy()->tenant?->features()->inactive(\App\Features\Customer\TierBase::class),
            'service' => tenancy()->tenant?->features()->inactive(\App\Features\Service\ServiceBase::class),
            default => false
        };
    }

    private static function refreshSelectAllState(Closure $get, Closure $set): void
    {
        $set('select_all', self::$permissionGroups->every(fn (PermissionGroup $permissionGroup, string $groupName) => $get($groupName)));
    }

    private static function refreshPermissionGroupState(string $groupName, PermissionGroup $permissionGroup, Closure $get, Closure $set): void
    {
        $set($groupName, $permissionGroup->abilities->pluck('id')->every(fn (int $id) => in_array($id, $get("{$groupName}_abilities"))));
    }

    private static function refreshPermissionGroupAbilitiesState(string $groupName, PermissionGroup $permissionGroup, Closure $get, Closure $set): void
    {
        $set("{$groupName}_abilities", $get($groupName) ? $permissionGroup->abilities->pluck('id')->toArray() : []);
    }

    public static function getRelations(): array
    {
        return [
            ActivitiesRelationManager::class,
        ];
    }
}
