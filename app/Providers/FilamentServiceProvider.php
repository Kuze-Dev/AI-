<?php

declare(strict_types=1);

namespace App\Providers;

use App\Filament\Livewire\Auth\AccountDeactivatedNotice;
use App\Filament\Livewire\Auth\ConfirmPassword;
use App\Filament\Livewire\Auth\EmailVerificationNotice;
use App\Filament\Livewire\Auth\RequestPasswordReset;
use App\Filament\Livewire\Auth\ResetPassword;
use App\Filament\Livewire\Auth\TwoFactorAuthentication;
use App\Filament\Livewire\Auth\VerifyEmail;
use Closure;
use Domain\Admin\Models\Admin;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Saade\FilamentLaravelLog\Pages\ViewLog;
use Filament\Pages\Actions as PageActions;
use Filament\Tables\Actions as TableActions;

/** @property \Illuminate\Foundation\Application $app */
class FilamentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        Filament::serving(function () {
            Filament::registerViteTheme('resources/css/filament/app.css');

            if (Filament::currentContext() !== 'filament') {
                return;
            }

            Filament::registerNavigationGroups([
                NavigationGroup::make('Access')
                    ->icon('heroicon-s-lock-closed'),
                NavigationGroup::make('System')
                    ->icon('heroicon-s-exclamation'),
            ]);
        });

        Filament::registerRenderHook(
            'footer.start',
            fn () => <<<HTML
                    <p>
                        Powered by
                        <a
                            href="https://halcyonwebdesign.com.ph/"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="transition hover:text-primary-500"
                        >
                            Halcyon Web Design
                        </a>
                    </p>
                HTML,
        );

        Filament::registerRenderHook(
            'head.end',
            fn () => Vite::withEntryPoints(['resources/js/filament/app.js'])->toHtml(),
        );

        ViewLog::can(fn (?Admin $admin) => $admin?->hasRole(config('domain.role.super_admin')));

        $this->registerRoutes();

        $this->configureComponents();
    }

    protected function registerRoutes(): void
    {
        Route::middleware(config('filament.middleware.base'))
            ->domain(config('filament.domain'))
            ->prefix('admin')
            ->name('filament.auth.')
            ->group(function () {
                Route::get('two-factor', TwoFactorAuthentication::class)
                    ->middleware('guest:admin')
                    ->name('two-factor');

                Route::prefix('password')
                    ->name('password.')
                    ->group(function () {
                        Route::get('reset', RequestPasswordReset::class)
                            ->middleware('guest:admin')
                            ->name('request');
                        Route::get('reset/{token}', ResetPassword::class)
                            ->middleware('guest:admin')
                            ->name('reset');
                        Route::get('confirm', ConfirmPassword::class)
                            ->middleware(\Filament\Http\Middleware\Authenticate::class)
                            ->name('confirm');
                    });

                Route::middleware(\Filament\Http\Middleware\Authenticate::class)
                    ->group(function () {
                        Route::get('account-deactivated', AccountDeactivatedNotice::class)
                            ->name('account-deactivated.notice');

                        Route::prefix('verify')
                            ->name('verification.')
                            ->group(function () {
                                Route::get('/', EmailVerificationNotice::class)
                                    ->name('notice');
                                Route::get('/{id}/{hash}', VerifyEmail::class)
                                    ->name('verify');
                            });
                    });
            });
    }

    protected function configureComponents(): void
    {
        PageActions\DeleteAction::configureUsing($this->createModalSubheadingConfiguration('delete'), isImportant: true);
        PageActions\RestoreAction::configureUsing($this->createModalSubheadingConfiguration('restore'), isImportant: true);
        PageActions\ForceDeleteAction::configureUsing($this->createModalSubheadingConfiguration('force delete'), isImportant: true);

        TableActions\DeleteAction::configureUsing($this->createModalSubheadingConfiguration('delete'), isImportant: true);
        TableActions\RestoreAction::configureUsing($this->createModalSubheadingConfiguration('restore'), isImportant: true);
        TableActions\ForceDeleteAction::configureUsing($this->createModalSubheadingConfiguration('force delete'), isImportant: true);

        TableActions\DeleteBulkAction::configureUsing($this->createBulkModalSubheadingConfiguration('delete'), isImportant: true);
        TableActions\RestoreBulkAction::configureUsing($this->createBulkModalSubheadingConfiguration('restore'), isImportant: true);
        TableActions\ForceDeleteBulkAction::configureUsing($this->createBulkModalSubheadingConfiguration('force delete'), isImportant: true);
    }

    private function createModalSubheadingConfiguration(string $verb): Closure
    {
        return fn (PageActions\Action|TableActions\Action $action) => $action->modalSubheading(
            fn (PageActions\Action|TableActions\Action $action) => trans(
                "Are you sure you want to {$verb} this :resource?",
                ['resource' => $action->getModelLabel() ?? 'record']
            )
        );
    }

    private function createBulkModalSubheadingConfiguration(string $verb): Closure
    {
        return fn (TableActions\BulkAction $action) => $action->modalSubheading(
            fn (TableActions\BulkAction $action) => trans(
                "Are you sure you want to {$verb} :count :resource/s?",
                [
                    'resource' => $action->getModelLabel(),
                    'count' => $action->getRecords()?->count() ?? 0,
                ]
            )
        );
    }
}
