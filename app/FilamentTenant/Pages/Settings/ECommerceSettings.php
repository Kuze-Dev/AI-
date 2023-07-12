<?php

declare(strict_types=1);

namespace App\FilamentTenant\Pages\Settings;

use App\Filament\Rules\FullyQualifiedDomainNameRule;
use App\Settings\ECommerceSettings as SettingsECommerceSettings;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\TextInput;

class ECommerceSettings extends TenantBaseSettings
{
    protected static string $settings = SettingsECommerceSettings::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $title = 'E Commerce Settings';

    protected function getFormSchema(): array
    {
        return [
            Card::make([
                TextInput::make('domain')
                    ->nullable()
                    ->rules([new FullyQualifiedDomainNameRule()])
                    ->maxLength(100)
                    ->columnSpan('full'),
            ])
                ->columns(2),
        ];
    }
}
