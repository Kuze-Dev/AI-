<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\AdminResource\Pages;
use Domain\Admin\Actions\SendEmailVerificationNotificationToAdminAction;
use Domain\Admin\Models\Admin;
use Domain\Auth\Actions\ForgotPasswordAction;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class AdminResource extends Resource
{
    protected static ?string $model = Admin::class;

    protected static ?string $navigationGroup = 'Access';

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static string|array $middlewares = ['password.confirm:admin.password.confirm'];

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
                        ->required(),
                    Forms\Components\TextInput::make('password')
                        ->password()
                        ->required()
                        ->rule(Password::default())
                        ->visible(fn (?Admin $record) => $record === null || ! $record->exists),
                    Forms\Components\TextInput::make('password_confirmation')
                        ->required()
                        ->password()
                        ->same('password')
                        ->dehydrated(false)
                        ->visible(fn (?Admin $record) => $record === null || ! $record->exists),
                    Forms\Components\Select::make('timezone')
                        ->options(collect(timezone_identifiers_list())->mapWithKeys(fn (string $timezone) => [$timezone => $timezone]))
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
                                ->relationship('roles', 'name')
                                ->saveRelationshipsUsing(null)
                                ->dehydrated(true)
                                ->multiple()
                                ->preload(),
                            Forms\Components\Select::make('permissions')
                                ->relationship('permissions', 'name')
                                ->saveRelationshipsUsing(null)
                                ->dehydrated(true)
                                ->multiple()
                                ->preload(),
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
                Tables\Columns\TextColumn::make('full_name')->sortable(),
                Tables\Columns\BooleanColumn::make('email_verified_at')
                    ->label(trans('Verified'))
                    ->getStateUsing(fn (Admin $record): bool => $record->hasVerifiedEmail()),
                Tables\Columns\BooleanColumn::make('active'),
                Tables\Columns\TagsColumn::make('roles.name'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->authorize('update'),
                Tables\Actions\DeleteAction::make()
                    ->authorize('delete'),
                Tables\Actions\RestoreAction::make()
                    ->authorize('restore'),
                Tables\Actions\ForceDeleteAction::make()
                    ->authorize('forceDelete'),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('resend-verification')
                        ->requiresConfirmation()
                        ->action(function (Admin $record, Tables\Actions\Action $action): void {
                            app(SendEmailVerificationNotificationToAdminAction::class)->execute($record);
                            $action->success();
                        })
                        ->authorize(fn (Admin $record) => Auth::user()?->can('resendVerification', $record) ?? false),
                    Tables\Actions\Action::make('send-password-reset')
                        ->requiresConfirmation()
                        ->action(function (Admin $record, Tables\Actions\Action $action): void {
                            $result = app(ForgotPasswordAction::class)->execute($record->email, 'admin');

                            if ($result->failed()) {
                                $action->failureNotificationMessage($result->getMessage())
                                    ->failure();

                                return;
                            }

                            $action->success();
                        })
                        ->authorize('sendPasswordReset'),
                ]),
            ])
            ->bulkActions([]);
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
}
