<?php

declare(strict_types=1);

namespace App\FilamentTenant\Pages\Settings;

use App\Filament\Pages\Settings\BaseSettings;
use App\FilamentTenant\Pages\Settings\Concerns\ContextualSettingsPage;
use App\FilamentTenant\Widgets\DeployStaticSite;
use App\Settings\CMSSettings as SettingsCMSSettings;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\TextInput;

class CMSSettings extends BaseSettings
{
    use ContextualSettingsPage;

    protected static string $settings = SettingsCMSSettings::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $title = 'CMS Settings';

    protected function getHeaderWidgets(): array
    {
        return [
            DeployStaticSite::class,
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            Card::make([
                TextInput::make('deploy_hook')
                    ->required()
                    ->url()
                    ->columnSpan('full'),
            ]),
        ];
    }

    protected function getBreadcrumbs(): array
    {
        $breadcrumb = $this->getBreadcrumb();

        return array_merge(
            [route('filament-tenant.pages.settings') => trans('Settings')],
            (filled($breadcrumb) ? [$breadcrumb] : [])
        );
    }
}
