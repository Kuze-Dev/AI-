<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Providers\Mixin\FilamentMountableActionMixin;
use App\Providers\Mixin\FilamentSelectFormMixin;
use App\Providers\Mixin\FilamentTextColumnMixin;
use Exception;
use Filament\Actions\Action as PageAction;
use Filament\Actions as PageActions;
use Filament\Actions\DeleteAction;
use Filament\Actions\Exports\Models\Export;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\Imports\Models\Import;
use Filament\Actions\MountableAction;
use Filament\Actions\RestoreAction;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Infolists;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Actions\Action as TableAction;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/** @property \Illuminate\Foundation\Application $app */
class CommonServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerMacros();

        $this->configureComponents();

        Page::$reportValidationErrorUsing = function (ValidationException $exception) {
            Notification::make()
                ->title($exception->getMessage())
                ->danger()
                ->send();
        };

        // https://github.com/filamentphp/filament/issues/10002#issuecomment-1837511287
        Import::polymorphicUserRelationship();

        // https://filamentphp.com/docs/3.x/actions/prebuilt-actions/export#using-a-polymorphic-user-relationship
        Export::polymorphicUserRelationship();
    }

    //    protected function registerRoutes(): void
    //    {
    //        Route::middleware(config('filament.middleware.base'))
    //            ->domain(config('filament.domain'))
    //            ->prefix('admin')
    //            ->name('filament.auth.')
    //            ->group(function () {
    //                Route::get('two-factor', TwoFactorAuthentication::class)
    //                    ->middleware('guest:admin')
    //                    ->name('two-factor');
    //
    //                Route::prefix('password')
    //                    ->name('password.')
    //                    ->group(function () {
    //                        Route::get('reset', RequestPasswordReset::class)
    //                            ->middleware('guest:admin')
    //                            ->name('request');
    //                        Route::get('reset/{token}', ResetPassword::class)
    //                            ->middleware('guest:admin')
    //                            ->name('reset');
    //                        Route::get('confirm', ConfirmPassword::class)
    //                            ->middleware(\Filament\Http\Middleware\Authenticate::class)
    //                            ->name('confirm');
    //                    });
    //
    //                Route::middleware(\Filament\Http\Middleware\Authenticate::class)
    //                    ->group(function () {
    //                        Route::get('account-deactivated', AccountDeactivatedNotice::class)
    //                            ->name('account-deactivated.notice');
    //
    //                        Route::prefix('verify')
    //                            ->name('verification.')
    //                            ->group(function () {
    //                                Route::get('/', EmailVerificationNotice::class)
    //                                    ->name('notice');
    //                                Route::get('/{id}/{hash}', VerifyEmail::class)
    //                                    ->name('verify');
    //                            });
    //                    });
    //            });
    //    }

    protected function registerMacros(): void
    {
        MountableAction::mixin(new FilamentMountableActionMixin());
        Tables\Columns\TextColumn::mixin(new FilamentTextColumnMixin());
        Forms\Components\Select::mixin(new FilamentSelectFormMixin());
    }

    protected function configureComponents(): void
    {
        Infolists\Components\TextEntry::configureUsing(
            function (Infolists\Components\TextEntry $component) {
                if (Filament::auth()->check()) {
                    $component
                        ->timezone(
                            /** @phpstan-ignore-next-line  */
                            Filament::auth()->user()->timezone
                        );
                }
            }
        );

        Forms\Components\DateTimePicker::configureUsing(
            function (Forms\Components\DateTimePicker $component): void {
                if (Filament::auth()->check()) {
                    $component
                        ->timezone(
                            /** @phpstan-ignore-next-line  */
                            Filament::auth()->user()->timezone
                        );
                }
            }
        );
        Tables\Columns\TextColumn::configureUsing(
            function (Tables\Columns\TextColumn $column): void {
                if (Filament::auth()->check()) {
                    $column
                        ->timezone(
                            /** @phpstan-ignore-next-line  */
                            Filament::auth()->user()->timezone
                        );
                }
            }
        );
        Tables\Table::configureUsing(
            fn (Tables\Table $table) => $table
                ->paginated([5, 10, 25, 50, 100])
        );
        Forms\Components\TextInput::configureUsing(
            fn (Forms\Components\TextInput $textInput) => $textInput
                ->maxLength(255)
        );

        $createActionConfiguration = fn (PageAction|TableAction $action) => $action
            ->withActivityLog(
                event: fn (MountableAction $action) => match ($action->getName()) {
                    'delete' => 'deleted',
                    'restore' => 'restored',
                    'forceDelete' => 'force-deleted',
                    default => throw new Exception(),
                },
                description: fn (PageAction|TableAction $action) => match ($action->getName()) {
                    'delete' => $action->getRecordTitle().' deleted',
                    'restore' => $action->getRecordTitle().' restored',
                    'forceDelete' => $action->getRecordTitle().' force deleted',
                    default => throw new Exception(),
                }
            )
            ->modalDescription(
                fn (PageAction|TableAction $action) => trans(
                    'Are you sure you want to :action this :resource?',
                    [
                        'action' => Str::of($action->getName() ?? '')->headline()->lower()->toString(),
                        'resource' => $action->getModelLabel() ?? 'record',
                    ]
                )
            )
            ->failureNotificationTitle(
                fn (PageAction|TableAction $action) => trans(
                    'Unable to :action :resource.',
                    [
                        'action' => Str::of($action->getName() ?? '')->headline()->lower()->toString(),
                        'resource' => $action->getModelLabel() ?? 'record',
                    ]
                )
            );

        $createBulkActionConfiguration = fn (Tables\Actions\BulkAction $action) => $action
            ->withActivityLog(
                event: fn (Tables\Actions\BulkAction $action) => match ($action->getName()) {
                    'delete' => 'deleted',
                    'restore' => 'restored',
                    'forceDelete' => 'force-deleted',
                    default => throw new Exception(),
                })
            ->modalDescription(
                fn (Tables\Actions\BulkAction $action) => trans(
                    'Are you sure you want to :action :count :resource/s?',
                    [
                        'action' => Str::of($action->getName() ?? '')->headline()->lower()->toString(),
                        'resource' => $action->getModelLabel(),
                        'count' => $action->getRecords()?->count() ?? 0,
                    ]
                )
            )
            ->failureNotificationTitle(
                fn (Tables\Actions\BulkAction $action) => trans(
                    'Unable to :action :resource/s.',
                    [
                        'action' => Str::of($action->getName() ?? '')->headline()->lower()->toString(),
                        'resource' => $action->getModelLabel(),
                    ]
                )
            );

        PageActions\CreateAction::configureUsing(fn (PageActions\CreateAction $action) => $action->icon('heroicon-o-plus-small'));
        DeleteAction::configureUsing($createActionConfiguration(...), isImportant: true);
        RestoreAction::configureUsing($createActionConfiguration(...), isImportant: true);
        ForceDeleteAction::configureUsing($createActionConfiguration(...), isImportant: true);

        Tables\Actions\DeleteAction::configureUsing($createActionConfiguration(...), isImportant: true);
        Tables\Actions\RestoreAction::configureUsing($createActionConfiguration(...), isImportant: true);
        Tables\Actions\ForceDeleteAction::configureUsing($createActionConfiguration(...), isImportant: true);

        Tables\Actions\DeleteBulkAction::configureUsing($createBulkActionConfiguration(...), isImportant: true);
        Tables\Actions\RestoreBulkAction::configureUsing($createBulkActionConfiguration(...), isImportant: true);
        Tables\Actions\ForceDeleteBulkAction::configureUsing($createBulkActionConfiguration(...), isImportant: true);
    }
}
