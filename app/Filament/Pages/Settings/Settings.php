<?php

declare(strict_types=1);

namespace App\Filament\Pages\Settings;

use Filament\Facades\Filament;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Page;
use Filament\Pages\SettingsPage;

class Settings extends Page
{
    protected static ?int $navigationSort = 1;

    protected static ?string $navigationGroup = 'System';

    protected static ?string $navigationIcon = 'heroicon-o-cog';

    protected static string $view = 'filament.pages.settings';

    protected function getBreadcrumbs(): array
    {
        return [
            self::getUrl() => trans('Settings'),
        ];
    }

    public static function getNavigationItems(): array
    {
        return [
            NavigationItem::make(static::getNavigationLabel())
                ->group(static::getNavigationGroup())
                ->icon(static::getNavigationIcon())
                ->isActiveWhen(fn (): bool => request()->routeIs('filament.pages.settings*'))
                ->sort(static::getNavigationSort())
                ->badge(static::getNavigationBadge(), color: static::getNavigationBadgeColor())
                ->url(static::getNavigationUrl()),
        ];
    }

    public function getViewData(): array
    {
        return [
            'settings' => collect(Filament::getPages())
                ->filter(fn (string $pageClass) => is_subclass_of($pageClass, SettingsPage::class)),
        ];
    }
}
