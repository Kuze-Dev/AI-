<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\Filament\Resources\AdminResource\Pages;
use App\Filament\Resources\AdminResource\RelationManagers\CauserRelationManager;
use Domain\Admin\Exports\AdminExporter;
use Domain\Admin\Models\Admin;
use Domain\Auth\Actions\ForgotPasswordAction;
use Exception;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Table;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Lloricode\Timezone\Timezone;
use STS\FilamentImpersonate\Tables\Actions\Impersonate;

class AdminResource extends Resource
{
    protected static ?string $model = Admin::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static string|array $routeMiddleware = RequirePassword::class.':filament.admin.password.confirm';

    protected static ?string $recordTitleAttribute = 'full_name';

    public static function getNavigationGroup(): ?string
    {
        return trans('Access');
    }

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
                Forms\Components\Section::make([
                    Forms\Components\TextInput::make('first_name')
                        ->required(),
                    Forms\Components\TextInput::make('last_name')
                        ->required(),
                    Forms\Components\TextInput::make('email')
                        ->email()
                        ->rules(Rule::email())
                        ->unique(ignoreRecord: true)
                        ->required()
                        ->helperText(fn (?Admin $record) => ! empty($record) && ! config('domain.admin.can_change_email') ? 'Email update is currently disabled.' : '')
                        ->disabled(fn (?Admin $record) => ! empty($record) && ! config('domain.admin.can_change_email')),
                    Forms\Components\TextInput::make('password')
                        ->password()
                        ->required()
                        ->rule(Password::default())
                        ->helperText(
                            app()->environment('local', 'testing')
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
                        ->options(Timezone::generateList())
                        ->rule('timezone')
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
                                ->preload()
                                ->relationship(
                                    titleAttribute: 'name',
                                    modifyQueryUsing: fn (Builder $query) => $query->where('guard_name', 'admin')
                                        ->where('name', '!=', config('domain.role.super_admin'))
                                ),
                            Forms\Components\Select::make('permissions')
                                ->multiple()
                                ->preload()
                                ->relationship(
                                    titleAttribute: 'name',
                                    modifyQueryUsing: fn (Builder $query) => $query->where('guard_name', 'admin')
                                ),
                        ]),
                ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    /** @throws Exception */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->sortable(['first_name', 'last_name'])
                    ->searchable(['first_name', 'last_name'])
                    ->truncate('xs', true),
                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label(trans('Verified'))
                    ->getStateUsing(fn (Admin $record): bool => $record->hasVerifiedEmail())
                    ->boolean(),
                Tables\Columns\IconColumn::make('active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->badge(),

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

            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\Action::make('resend-verification')
                        ->requiresConfirmation()
                        ->icon('heroicon-o-envelope')
                        ->action(function (Admin $record, Tables\Actions\Action $action): void {
                            try {
                                $record->sendEmailVerificationNotification();
                                $action
                                    ->successNotificationTitle(trans('A fresh verification link has been sent to your email address.'))
                                    ->success();
                            } catch (Exception) {
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
                                ->execute($record->email, Filament::getAuthPasswordBroker());

                            if ($result->failed()) {
                                $action->failureNotificationTitle($result->getMessage())
                                    ->failure();

                                return;
                            }

                            $action
                                ->successNotificationTitle(trans('A password reset link has been sent to your email address.'))
                                ->success();
                        })
                        ->authorize('sendPasswordReset')
                        ->withActivityLog(
                            event: 'password-reset-link-sent',
                            description: fn (Admin $record) => $record->full_name.' password reset sent'
                        ),
                    Impersonate::make()
                        ->translateLabel()
                        ->grouped()
                        ->guard(Filament::getAuthGuard())
                        ->redirectTo(Filament::getUrl() ?? '/')
                        ->authorize('impersonate')
                        ->withActivityLog(
                            event: 'impersonated',
                            description: fn (Admin $record) => $record->full_name.' impersonated',
                            causedBy: Filament::auth()->user()
                        ),
                ]),
            ])
            ->bulkActions([
                ExportBulkAction::make()
                    ->exporter(AdminExporter::class)
                    ->withActivityLog(
                        event: 'bulk-exported',
                        description: fn (ExportBulkAction $action) => 'Bulk Exported '.$action->getModelLabel(),
                        properties: fn (ExportBulkAction $action) => [
                            'selected_record_ids' => $action->getRecords()
                                ?->map(
                                    function (int|string|Admin $model): Admin {
                                        if ($model instanceof Admin) {
                                            return $model;
                                        }

                                        return Admin::whereKey($model)->first();
                                    }
                                ),
                        ]
                    ),
            ])
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
            CauserRelationManager::class,
        ];
    }
}
