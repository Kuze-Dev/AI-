<?php

declare(strict_types=1);

namespace App\Filament\Admin\Themes;

use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\Support\Colors\Color;
use Hasnayeen\Themes\Contracts\CanModifyPanelConfig;
use Hasnayeen\Themes\Contracts\HasChangeableColor;
use Hasnayeen\Themes\Contracts\Theme;
use Illuminate\Support\Arr;

class Mint implements CanModifyPanelConfig, HasChangeableColor, Theme
{
    public static function getName(): string
    {
        return 'mint';
    }

    public static function getPath(): string
    {
        return 'resources/css/filament/tenant/themes/mint.css';
    }

    public function getThemeColor(): array
    {
        return Arr::except(Color::all(), ['gray', 'zinc', 'neutral', 'stone']);
    }

    public function getPrimaryColor(): array
    {
        return ['primary' => $this->getThemeColor()['green']];
    }

    public function modifyPanelConfig(Panel $panel): Panel
    {
        return $panel
            ->viteTheme($this->getPath())
            ->widgets([
                \App\FilamentTenant\Widgets\CmsMenuWidget::class,
            ])
            ->navigationGroups([
                NavigationGroup::make()->label(fn () => trans('CMS')),
                NavigationGroup::make()->label(fn () => trans('eCommerce')),
                NavigationGroup::make()->label(fn () => trans('Access')),
                NavigationGroup::make()->label(fn () => trans('Shop Configuration')),
                NavigationGroup::make()->label(fn () => trans('Service Management')),
                NavigationGroup::make()->label(fn () => trans('Customer Management')),
                NavigationGroup::make()->label(fn () => trans('System')),
            ])
            ->topNavigation(true)
            ->sidebarCollapsibleOnDesktop(false);
    }
}
