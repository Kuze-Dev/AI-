<?php

declare(strict_types=1);

namespace App\Providers;

use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;
use Illuminate\Support\ServiceProvider;

/** @property \Illuminate\Foundation\Application $app */
class FilamentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        Filament::serving(function () {
            Filament::registerNavigationGroups([

                NavigationGroup::make('Access')
                    ->icon('heroicon-s-lock-closed'),

                NavigationGroup::make('Settings')
                    ->icon('heroicon-s-cog'),

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
                            class="text-gray-300 hover:text-primary-500 transition"
                        >
                            Halcyon Web Design
                        </a>
                    </p>

                HTML,
        );
    }
}
