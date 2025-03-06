<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Features\CMS\SitesManagement;
use App\Features\Customer\CustomerBase;
use App\Features\Customer\TierBase;
use App\Features\ECommerce\ECommerceBase;
use App\Features\Service\ServiceBase;
use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\Filament\Resources\RoleResource\Pages;
use App\Filament\Resources\RoleResource\Support\PermissionGroup;
use App\Filament\Resources\RoleResource\Support\PermissionGroupCollection;
use Domain\Role\Actions\DeleteRoleAction;
use Domain\Role\Exceptions\CantDeleteRoleWithAssociatedUsersException;
use Domain\Role\Models\Role;
use Domain\Tenant\TenantFeatureSupport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use JulioMotol\FilamentPasswordConfirmation\RequiresPasswordConfirmation;
use Spatie\Permission\Models\Permission;
use Support\ConstraintsRelationships\Exceptions\DeleteRestrictedException;

class RoleResource extends Resource
{
    use RequiresPasswordConfirmation;

    public static PermissionGroupCollection $permissionGroups;

    protected static ?string $model = Role::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    #[\Override]
    public static function getNavigationGroup(): ?string
    {
        return trans('Access');
    }

    #[\Override]
    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

    #[\Override]
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Select::make('guard_name')
                        ->default(config()->string('auth.defaults.guard'))
                        ->options(self::getGuards()->mapWithKeys(fn (string $guardName) => [$guardName => $guardName]))
                        ->required()
                        ->reactive(),
                ])
                    ->columns(['lg' => 2]),

                Forms\Components\Section::make('Permissions')
                    ->schema(self::generatePermissionGroupsFormSchema(...)),
            ]);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->formatStateUsing(fn ($state): string => Str::headline($state))
                    ->searchable(),
                Tables\Columns\TextColumn::make('guard_name')
                    ->badge(),
                Tables\Columns\TextColumn::make('permissions_count')
                    ->badge()
                    ->counts('permissions')
                    ->colors(['success']),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('guard_name')
                    ->options(self::getGuards()->mapWithKeys(fn (string $guardName) => [$guardName => $guardName])),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            ->defaultSort('updated_at', 'desc');
    }

    #[\Override]
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
        return (new Collection(config()->array('auth.guards')))
            ->reject(fn (array $config, string $guard) => $guard === 'sanctum')
            ->map(fn (array $config, string $guard) => $guard);
    }

    /** @return array<\Filament\Forms\Components\Component> */
    private static function generatePermissionGroupsFormSchema(Forms\Get $get): array
    {
        /** @var string $guard */
        $guard = $get('guard_name');

        if (blank($guard)) {
            return [
                Forms\Components\Placeholder::make(trans('Please select a guard')),
            ];
        }

        self::$permissionGroups = PermissionGroupCollection::make(['guard_name' => $guard]);

        if (self::$permissionGroups->isEmpty()) {
            return [
                Forms\Components\Placeholder::make(trans('No Available Permissions for the selected guard')),
            ];
        }

        return [
            Forms\Components\Hidden::make('permissions')
                ->reactive()
                ->formatStateUsing(fn (?Role $record) => $record ? $record->permissions->pluck('id') : [])
                ->dehydrateStateUsing(fn (Forms\Get $get): array => self::$permissionGroups->reduce(
                    function (array $permissions, PermissionGroup $permissionGroup, string $groupName) use ($get): array {
                        if ($get($groupName) ?? false) {
                            array_push($permissions, $permissionGroup->main->id);
                        } else {
                            $permissions = array_merge($permissions, $get("{$groupName}_abilities") ?? []);
                        }

                        return $permissions;
                    },
                    []
                )),
            Forms\Components\Toggle::make('select_all')
                ->onIcon('heroicon-s-shield-check')
                ->offIcon('heroicon-s-shield-exclamation')
                ->helperText(trans('Enable all Permissions for this role'))
                ->reactive()
                ->formatStateUsing(fn (?Role $record) => self::$permissionGroups->every(fn (PermissionGroup $permissionGroup): bool => $record?->hasPermissionTo($permissionGroup->main) ?? false))
                ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, bool $state): void {
                    self::$permissionGroups->each(function (PermissionGroup $permissionGroup, string $groupName) use ($get, $set, $state): void {
                        $set($groupName, $state);

                        self::refreshPermissionGroupAbilitiesState($groupName, $permissionGroup, $get, $set);
                    });
                })
                ->dehydrated(false),
            Forms\Components\Grid::make(['sm' => 2])
                ->schema(
                    self::$permissionGroups->map(
                        fn (PermissionGroup $permissionGroup, string $groupName) => Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\Toggle::make($groupName)
                                    ->label(Str::headline($groupName))
                                    ->helperText('Enable all abilities for this resource')
                                    ->onIcon('heroicon-s-lock-open')
                                    ->offIcon('heroicon-s-lock-closed')
                                    ->reactive()
                                    ->formatStateUsing(fn (?Role $record) => $record?->hasPermissionTo($permissionGroup->main))
                                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) use ($groupName, $permissionGroup): void {
                                        self::refreshPermissionGroupAbilitiesState($groupName, $permissionGroup, $get, $set);
                                        self::refreshSelectAllState($get, $set);
                                    })
                                    ->dehydrated(false),
                                Forms\Components\Fieldset::make('Abilities')
                                    ->schema([
                                        Forms\Components\CheckboxList::make("{$groupName}_abilities")
                                            ->label('')
                                            ->options($permissionGroup->abilities->mapWithKeys(
                                                fn (Permission $permission) => [
                                                    $permission->id => Str::headline(explode('.', $permission->name, 2)[1]),
                                                ]
                                            ))
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
                                            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) use ($groupName, $permissionGroup): void {
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
        $feature = match ($groupName) {
            'country',
            'shippingMethod',
            'currency',
            'discount',
            'order',
            'product',
            'paymentMethod',
            'taxZone',
            'site' => SitesManagement::class,
            'ecommerceSettings' => ECommerceBase::class,
            'customers' => CustomerBase::class,
            'tier' => TierBase::class,
            'service' => ServiceBase::class,
            default => false
        };

        if ($feature === false) {
            return false;
        }

        return TenantFeatureSupport::inactive($feature);
    }

    private static function refreshSelectAllState(Forms\Get $get, Forms\Set $set): void
    {
        $set('select_all', self::$permissionGroups->every(fn (PermissionGroup $permissionGroup, string $groupName) => $get($groupName)));
    }

    private static function refreshPermissionGroupState(string $groupName, PermissionGroup $permissionGroup, Forms\Get $get, Forms\Set $set): void
    {
        $set($groupName, $permissionGroup->abilities->pluck('id')->every(fn (int $id) => in_array($id, $get("{$groupName}_abilities"))));
    }

    private static function refreshPermissionGroupAbilitiesState(string $groupName, PermissionGroup $permissionGroup, Forms\Get $get, Forms\Set $set): void
    {
        $set("{$groupName}_abilities", $get($groupName) ? $permissionGroup->abilities->pluck('id')->toArray() : []);
    }

    #[\Override]
    public static function getRelations(): array
    {
        return [
            ActivitiesRelationManager::class,
        ];
    }
}
