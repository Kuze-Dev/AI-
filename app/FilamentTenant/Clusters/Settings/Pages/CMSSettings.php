<?php

declare(strict_types=1);

namespace App\FilamentTenant\Clusters\Settings\Pages;

use App\Filament\Rules\FullyQualifiedDomainNameRule;
use App\FilamentTenant\Support\Concerns\AuthorizeCMSSettings;
use App\FilamentTenant\Widgets\DeployStaticSite;
use App\Settings\CMSSettings as SettingsCMSSettings;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\TextInput;

class CMSSettings extends TenantBaseSettings
{
    use AuthorizeCMSSettings;

    protected static string $settings = SettingsCMSSettings::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $title = 'CMS Settings';

    #[\Override]
    protected function getHeaderWidgets(): array
    {
        return [
            DeployStaticSite::class,
        ];
    }

    #[\Override]
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
                TextInput::make('front_end_domain')
                    ->nullable()
                    ->rules([new FullyQualifiedDomainNameRule()])
                    ->maxLength(100)
                    ->columnSpan('full'),
            ]),
        ];
    }
}
