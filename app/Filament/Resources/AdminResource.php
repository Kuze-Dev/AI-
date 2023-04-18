<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\Filament\Resources\AdminResource\Pages;
use Domain\Admin\Models\Admin;
use Domain\Auth\Actions\ForgotPasswordAction;
use Domain\Role\Models\Role;
use Exception;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Filters\Layout;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Permission;
use STS\FilamentImpersonate\Impersonate;

class AdminResource extends Resource
{
    protected static ?string $model = Admin::class;

    protected static ?string $navigationGroup = 'Access';

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static string|array $middlewares = ['password.confirm:filament.auth.password.confirm'];

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
                    Forms\Components\TextInput::make('first_name')
                        ->required(),
                    Forms\Components\TextInput::make('last_name')
                        ->required(),
                    Forms\Components\TextInput::make('email')
                        ->email()
                        ->rules('email:rfc,dns')
                        ->required()
                        ->helperText(fn (?Admin $record) => ! empty($record) && ! config('domain.admin.can_change_email') ? 'Email update is currently disabled.' : '')
                        ->disabled(fn (?Admin $record) => ! empty($record) && ! config('domain.admin.can_change_email')),
                    Forms\Components\TextInput::make('password')
                        ->password()
                        ->required()
                        ->rule(Password::default())
                        ->helperText(
                            fn () => config('app.env') === 'local' || config('app.env') === 'testing'
                                ? trans('Password must be at least 4 characters.')
                                : trans('Password must be at least 8 characters, have 1 special character, 1 number, 1 upper case and 1 lower case.')
                        )
                        ->visible(fn (?Admin $record) => $record === null || ! $record->exists),
                    Forms\Components\TextInput::make('password_confirmation')
                        ->required()
                        ->password()
                        ->same('password')
                        ->dehydrated(false)
                        ->rule(Password::default())
                        ->visible(fn (?Admin $record) => $record === null || ! $record->exists),
                    Forms\Components\Select::make('timezone')
                        ->options(
                            collect(timezone_identifiers_list())
                                ->mapWithKeys(fn (string $timezone) => [$timezone => $timezone])
                                ->toArray()
                        )
                        ->searchable()
                        ->default(config('domain.admin.default_timezone')),
                ])
                    ->columnSpan(['lg' => 2]),
                Forms\Components\Group::make([
                    Forms\Components\Section::make(trans('Status'))
                        ->schema([
                            Forms\Components\Toggle::make('active')
                                ->default(true),
                        ]),
                    Forms\Components\Section::make(trans('Access'))
                        ->schema([
                            Forms\Components\Select::make('roles')
                                ->multiple()
                                ->options(
                                    fn () => Role::orderBy('name')
                                        ->pluck('name', 'id')
                                        ->toArray()
                                )
                                ->afterStateHydrated(function (Forms\Components\Select $component, ?Admin $record): void {
                                    $component->state($record ? $record->roles->pluck('id')->toArray() : []);
                                }),
                            Forms\Components\Select::make('permissions')
                                ->multiple()
                                ->options(
                                    fn () => Permission::orderBy('name')
                                        ->pluck('name', 'id')
                                        ->toArray()
                                )
                                ->afterStateHydrated(function (Forms\Components\Select $component, ?Admin $record): void {
                                    $component->state($record ? $record->permissions->pluck('id')->toArray() : []);
                                }),
                        ]),
                ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->sortable()
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        /** @var Builder|Admin $query */
                        return $query
                            ->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    }),
                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label(trans('Verified'))
                    ->getStateUsing(fn (Admin $record): bool => $record->hasVerifiedEmail())
                    ->boolean(),
                Tables\Columns\IconColumn::make('active')
                    ->boolean(),
                Tables\Columns\TagsColumn::make('roles.name'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('role')
                    ->options(
                        app(config('permission.models.role'))
                            ->pluck('name', 'id')
                            ->put('no-roles', 'No Roles')
                    )
                    ->query(function (Builder $query, array $data) {
                        $query->when(filled($data['value']), function (Builder $query) use ($data) {
                            /** @var Admin|Builder $query */

                            if ($data['value'] === 'no-roles') {
                                $query->whereDoesntHave('roles');

                                return;
                            }
                            $query->role($data['value']);
                        });
                    }),
                Tables\Filters\SelectFilter::make('active')
                    ->options(['1' => 'Active', '0' => 'Inactive'])
                    ->query(function (Builder $query, array $data) {
                        $query->when(filled($data['value']), function (Builder $query) use ($data) {
                            $query->when(filled($data['value']), function (Builder $query) use ($data) {
                                /** @var Admin|Builder $query */
                                match ($data['value']) {
                                    '1' => $query->where('active', true),
                                    '0' => $query->where('active', false),
                                    default => '',
                                };
                            });
                        });
                    }),
                Tables\Filters\SelectFilter::make('email_verified')
                    ->options(['1' => 'Verified', '0' => 'Not Verified'])
                    ->query(function (Builder $query, array $data) {
                        $query->when(filled($data['value']), function (Builder $query) use ($data) {
                            /** @var Admin|Builder $query */
                            match ($data['value']) {
                                '1' => $query->whereNotNull('email_verified_at'),
                                '0' => $query->whereNull('email_verified_at'),
                                default => '',
                            };
                        });
                    }),
            ])
            ->filtersLayout(Layout::AboveContent)
            ->actions([
                Tables\Actions\EditAction::make()
                    ->authorize('update'),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DeleteAction::make()
                        ->authorize('delete'),
                    Tables\Actions\RestoreAction::make()
                        ->authorize('restore'),
                    Tables\Actions\ForceDeleteAction::make()
                        ->authorize('forceDelete'),
                    Tables\Actions\Action::make('resend-verification')
                        ->requiresConfirmation()
                        ->action(function (Admin $record, Tables\Actions\Action $action): void {
                            try {
                                $record->sendEmailVerificationNotification();
                                $action
                                    ->successNotificationTitle(trans('A fresh verification link has been sent to your email address.'))
                                    ->success();
                            } catch (Exception $e) {
                                $action->failureNotificationTitle(trans('Failed to send verification link.'))
                                    ->failure();
                            }
                        })
                        ->authorize('resendVerification'),
                    Tables\Actions\Action::make('send-password-reset')
                        ->requiresConfirmation()
                        ->icon('heroicon-o-lock-open')
                        ->action(function (Admin $record, Tables\Actions\Action $action): void {
                            $result = app(ForgotPasswordAction::class)
                                ->execute($record->email, 'admin');

                            if ($result->failed()) {
                                $action->failureNotificationTitle($result->getMessage())
                                    ->failure();

                                return;
                            }

                            $action
                                ->successNotificationTitle(trans('A password reset link has been sent to your email address.'))
                                ->success();
                        })
                        ->authorize('sendPasswordReset'),
                    Impersonate::make()
                        ->guard('admin')
                        ->redirectTo(route(tenancy()->initialized ? 'filament-tenant.pages.dashboard' : 'filament.pages.dashboard'))
                        ->authorize('impersonate'),
                ]),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdmins::route('/'),
            'create' => Pages\CreateAdmin::route('/create'),
            'edit' => Pages\EditAdmin::route('/{record}/edit'),
        ];
    }

    /** @return Builder<Admin> */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ActivitiesRelationManager::class,
        ];
    }
}
