<?php

declare(strict_types=1);

namespace App\FilamentTenant\Pages\Settings;

use App\FilamentTenant\Widgets\DeployStaticSite;
use App\Settings\CMSSettings as SettingsCMSSettings;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\TextInput;

class CMSSettings extends TenantBaseSettings
{
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
            Card::make([
                TextInput::make('front_end_preview_page_url')
                    ->label('Front end preview page url ( URL must have "{slug}" )')
                    ->columnSpan('full'),
            ]),
        ];
    }
}
