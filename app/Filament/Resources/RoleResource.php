<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use App\Filament\Resources\RoleResource\Support\PermissionGroup;
use App\Filament\Resources\RoleResource\Support\PermissionGroupCollection;
use Closure;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleResource extends Resource
{
    public static PermissionGroupCollection $permissionGroups;

    protected static ?string $model = Role::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationGroup = 'Access';

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static string | array $middlewares = ['password.confirm:admin.password.confirm'];

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make([
                    Forms\Components\TextInput::make('name')
                        ->required()
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
                    ->dateTime(timezone: Auth::user()?->timezone),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([]);
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
            Forms\Components\Toggle::make('select_all')
                ->onIcon('heroicon-s-shield-check')
                ->offIcon('heroicon-s-shield-exclamation')
                ->helperText(trans('Enable all Permissions for this role'))
                ->reactive()
                ->afterStateHydrated(self::selectAllHydrated(...))
                ->afterStateUpdated(self::selectAllUpdated(...))
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
                                    ->afterStateHydrated(self::permissionGroupStateHydrated($permissionGroup))
                                    ->afterStateUpdated(self::permissionGroupStateUpdated($groupName, $permissionGroup))
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
                                            ->afterStateHydrated(self::permissionGroupAbilitiesStateHydrated($permissionGroup))
                                            ->afterStateUpdated(self::permissionGroupAbilitiesStateUpdated($groupName, $permissionGroup))
                                            ->dehydrated(false),
                                    ])
                                    ->columns(1),
                            ])
                            ->columnSpan(1)
                    )
                        ->toArray()
                ),
            Forms\Components\Hidden::make('permissions')
                ->dehydrateStateUsing(self::permissionsDehydrated(...)),
        ];
    }

    private static function selectAllHydrated(Forms\Components\Toggle $component, ?Role $record): void
    {
        if (! $record) {
            return;
        }

        foreach (self::$permissionGroups as $permissionGroup) {
            if (! $record->hasPermissionTo($permissionGroup->main)) {
                return;
            }
        }

        $component->state(true);
    }

    private static function selectAllUpdated(Closure $get, Closure $set, bool $state): void
    {
        self::$permissionGroups->each(function (PermissionGroup $permissionGroup, string $groupName) use ($get, $set, $state): void {
            $set($groupName, $state);

            self::permissionGroupStateUpdated($groupName, $permissionGroup)($get, $set);
        });
    }

    private static function permissionGroupStateHydrated(PermissionGroup $permissionGroup): Closure
    {
        return function (Forms\Components\Toggle $component, ?Role $record) use ($permissionGroup): void {
            if (! $record) {
                return;
            }

            $component->state($record->hasPermissionTo($permissionGroup->main));
        };
    }

    private static function permissionGroupStateUpdated(string $groupName, PermissionGroup $permissionGroup): Closure
    {
        return function (Closure $get, Closure $set) use ($groupName, $permissionGroup): void {
            $set(
                "{$groupName}_abilities",
                $get($groupName)
                    ? $permissionGroup->abilities->pluck('id')
                    ->merge($get("{$groupName}_abilities"))
                    ->unique()
                    : $permissionGroup->abilities->pluck('id')
                    ->diff($get("{$groupName}_abilities"))
                    ->values()
            );

            self::refreshSelectAllState($get, $set);
        };
    }

    private static function permissionGroupAbilitiesStateHydrated(PermissionGroup $permissionGroup): Closure
    {
        return function (Forms\Components\CheckboxList $component, ?Role $record) use ($permissionGroup): void {
            if (! $record) {
                return;
            }

            $state = $record->hasPermissionTo($permissionGroup->main)
                ? array_keys($component->getOptions())
                : $record->permissions->pluck('id')
                ->intersect(array_keys($component->getOptions()))
                ->values()
                ->toArray();

            $component->state($state);
        };
    }

    private static function permissionGroupAbilitiesStateUpdated(string $groupName, PermissionGroup $permissionGroup): Closure
    {
        return function (Closure $get, Closure $set) use ($groupName, $permissionGroup): void {
            $selectedPermissionsInGroup = $permissionGroup->abilities->pluck('id')
                ->intersect($get("{$groupName}_abilities"));

            $set($groupName, $selectedPermissionsInGroup->count() === $permissionGroup->abilities->count());

            self::refreshSelectAllState($get, $set);
        };
    }

    private static function refreshSelectAllState(Closure $get, Closure $set): void
    {
        foreach (self::$permissionGroups->keys() as $groupName) {
            if (! $get($groupName)) {
                $set('select_all', false);

                return;
            }
        }

        $set('select_all', true);
    }

    private static function permissionsDehydrated(Closure $get): array
    {
        return self::$permissionGroups->reduce(
            function (array $permissions, PermissionGroup $permissionGroup, string $groupName) use ($get): array {
                if ($get($groupName) ?? false) {
                    array_push($permissions, $permissionGroup->main->id);
                } else {
                    $permissions = array_merge($permissions, $get("{$groupName}_abilities"));
                }

                return $permissions;
            },
            []
        );
    }
}
