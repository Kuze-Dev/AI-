<?php

declare(strict_types=1);
//
//declare(strict_types=1);
//
//namespace App\Providers\Filament;
//
//use App\FilamentTenant\Livewire\Auth;
//use App\FilamentTenant\Middleware\Authenticate;
//use App\FilamentTenant\Pages\Auth\Account;
//use Artificertech\FilamentMultiContext\ContextServiceProvider;
//use Artificertech\FilamentMultiContext\Http\Middleware\ApplyContext;
//use Filament\Facades\Filament;
//use Filament\Navigation\NavigationGroup;
//use Filament\Navigation\UserMenuItem;
//use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Blade;
//use Illuminate\Support\Facades\Route;
//
//class FilamentTenantServiceProvider extends ContextServiceProvider
//{
//    public static string $name = 'filament-tenant';
//
//    public function packageBooted(): void
//    {
//        parent::packageBooted();
//
//        Filament::serving(function () {
//            if (Filament::currentContext() !== static::$name) {
//                return;
//            }
//
//            Filament::registerUserMenuItems([
//                'account' => UserMenuItem::make()
//                    ->url(
//                        Filament::auth()->user()?->isZeroDayAdmin()
//                            ? null
//                            : Account::getUrl()
//                    ),
//            ]);
//
//            Filament::registerNavigationGroups([
//                NavigationGroup::make(trans('Shop Configuration'))
//                    ->icon('heroicon-o-cog'),
//                NavigationGroup::make(trans('Customer Management'))
//                    ->icon('heroicon-s-users'),
//                NavigationGroup::make(trans('CMS'))
//                    ->icon('heroicon-s-document-text'),
//                NavigationGroup::make(trans('eCommerce'))
//                    ->icon('heroicon-s-shopping-bag'),
//                NavigationGroup::make(trans('Access'))
//                    ->icon('heroicon-s-lock-closed'),
//                NavigationGroup::make(trans('System'))
//                    ->icon('heroicon-s-exclamation'),
//            ]);
//
//            Filament::registerRenderHook(
//                'body.start',
//                static fn (): string => Blade::render('<x-filament-impersonate::banner/>')
//            );
//        });
//
//        $this->registerRoutes();
//    }
//
//    protected function getUserMenuItems(): array
//    {
//        return [
//            'logout' => UserMenuItem::make()->url(route('filament-tenant.auth.logout')),
//        ];
//    }
//
//    protected function registerRoutes(): void
//    {
//        Route::middleware(array_merge([ApplyContext::class.':'.static::$name], $this->contextConfig('middleware.base')))
//            ->domain($this->contextConfig('domain'))
//            ->prefix('admin')
//            ->name(static::$name.'.auth.')
//            ->group(function () {
//                Route::get('login', Auth\Login::class)
//                    ->middleware('guest:admin')
//                    ->name('login');
//                Route::get('two-factor', Auth\TwoFactorAuthentication::class)
//                    ->middleware('guest:admin')
//                    ->name('two-factor');
//
//                Route::prefix('password')
//                    ->name('password.')
//                    ->group(function () {
//                        Route::get('reset', Auth\RequestPasswordReset::class)
//                            ->middleware('guest:admin')
//                            ->name('request');
//                        Route::get('reset/{token}', Auth\ResetPassword::class)
//                            ->middleware('guest:admin')
//                            ->name('reset');
//                        Route::get('confirm', Auth\ConfirmPassword::class)
//                            ->middleware(Authenticate::class)
//                            ->name('confirm');
//                    });
//
//                Route::middleware(Authenticate::class)
//                    ->group(function () {
//                        Route::get('account-deactivated', Auth\AccountDeactivatedNotice::class)
//                            ->name('account-deactivated.notice');
//
//                        Route::prefix('verify')
//                            ->name('verification.')
//                            ->group(function () {
//                                Route::get('/', Auth\EmailVerificationNotice::class)
//                                    ->name('notice');
//                                Route::get('/{id}/{hash}', Auth\VerifyEmail::class)
//                                    ->name('verify');
//                            });
//
//                        Route::post('logout', function (Request $request) {
//                            Filament::auth()->logout();
//
//                            $request->session()->invalidate();
//                            $request->session()->regenerateToken();
//
//                            return redirect()->route(static::$name.'.auth.login');
//                        })->name('logout');
//                    });
//            });
//    }
//}
