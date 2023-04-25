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
use Filament\Forms;
use Filament\Navigation\NavigationGroup;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Saade\FilamentLaravelLog\Pages\ViewLog;
use Filament\Pages\Actions as PageActions;
use Filament\Tables\Actions as TableActions;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

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

        $this->registerFormComponentMacros();

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

    protected function registerFormComponentMacros(): void
    {
        Forms\Components\FileUpload::macro('mediaLibraryCollection', function (string $collection) {
            /** @var Forms\Components\FileUpload $this */
            $this->multiple(
                fn ($record) => $record && ! $record->getRegisteredMediaCollections()
                    ->firstWhere('name', $collection)
                    ->singleFile
            );

            $this->formatStateUsing(
                fn (?HasMedia $record) => $record ? $record->getMedia($collection)
                    ->mapWithKeys(fn (Media $media) => [$media->uuid => $media->uuid])
                    ->toArray() : []
            );

            $this->beforeStateDehydrated(null);

            $this->dehydrateStateUsing(function (Forms\Components\FileUpload $component, ?array $state) {
                $files = array_values($state ?? []);

                return $component->isMultiple() ? $files : ($files[0] ?? null);
            });

            $this->getUploadedFileUrlUsing(static function (Forms\Components\FileUpload $component, string $file): ?string {
                $mediaClass = config('media-library.media_model', Media::class);

                /** @var ?Media $media */
                $media = $mediaClass::findByUuid($file);

                if ($media === null) {
                    return null;
                }

                if ($component->getVisibility() === 'private') {
                    try {
                        return $media->getTemporaryUrl(now()->addMinutes(5));
                    } catch (Throwable $exception) {
                        // This driver does not support creating temporary URLs.
                    }
                }

                return $media->getUrl();
            });

            return $this;
        });
    }

    protected function configureComponents(): void
    {
        PageActions\DeleteAction::configureUsing($this->createActionConfiguration(), isImportant: true);
        PageActions\RestoreAction::configureUsing($this->createActionConfiguration(), isImportant: true);
        PageActions\ForceDeleteAction::configureUsing($this->createActionConfiguration(), isImportant: true);

        TableActions\DeleteAction::configureUsing($this->createActionConfiguration(), isImportant: true);
        TableActions\RestoreAction::configureUsing($this->createActionConfiguration(), isImportant: true);
        TableActions\ForceDeleteAction::configureUsing($this->createActionConfiguration(), isImportant: true);

        TableActions\DeleteBulkAction::configureUsing($this->createBulkActionConfiguration(), isImportant: true);
        TableActions\RestoreBulkAction::configureUsing($this->createBulkActionConfiguration(), isImportant: true);
        TableActions\ForceDeleteBulkAction::configureUsing($this->createBulkActionConfiguration(), isImportant: true);
    }

    private function createActionConfiguration(): Closure
    {
        return fn (PageActions\Action|TableActions\Action $action) => $action
            ->modalSubheading(
                fn (PageActions\Action|TableActions\Action $action) => trans(
                    'Are you sure you want to :action this :resource?',
                    [
                        'action' => Str::of($action->getName())->headline()->lower()->toString(),
                        'resource' => $action->getModelLabel() ?? 'record',
                    ]
                )
            )->failureNotificationTitle(
                fn (PageActions\Action|TableActions\Action $action) => trans(
                    'Unable to :action :resource.',
                    [
                        'action' => Str::of($action->getName())->headline()->lower()->toString(),
                        'resource' => $action->getModelLabel() ?? 'record',
                    ]
                )
            );
    }

    private function createBulkActionConfiguration(): Closure
    {
        return fn (TableActions\BulkAction $action) => $action
            ->modalSubheading(
                fn (TableActions\BulkAction $action) => trans(
                    'Are you sure you want to :action :count :resource/s?',
                    [
                        'action' => Str::of($action->getName())->headline()->lower()->toString(),
                        'resource' => $action->getModelLabel(),
                        'count' => $action->getRecords()?->count() ?? 0,
                    ]
                )
            )
            ->failureNotificationTitle(
                fn (PageActions\Action|TableActions\Action $action) => trans(
                    'Unable to :action :resource/s.',
                    [
                        'action' => Str::of($action->getName())->headline()->lower()->toString(),
                        'resource' => $action->getModelLabel() ?? 'record',
                    ]
                )
            );
    }
}
