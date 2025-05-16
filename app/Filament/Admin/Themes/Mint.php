<?php

namespace App\Filament\Admin\Themes;

use Filament\Panel;
use Hasnayeen\Themes\Contracts\CanModifyPanelConfig;
use Hasnayeen\Themes\Contracts\HasChangeableColor;
use Hasnayeen\Themes\Contracts\Theme;
use Illuminate\Support\Arr;
use Filament\Support\Colors\Color;

class Mint implements HasChangeableColor, CanModifyPanelConfig, Theme
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
        return ['primary' => $this->getThemeColor()['blue']];
    }

    public function modifyPanelConfig(Panel $panel): Panel
    {
        return $panel
            ->viteTheme($this->getPath())
            ->topNavigation(true)
            ->sidebarCollapsibleOnDesktop(false);
    }
}
