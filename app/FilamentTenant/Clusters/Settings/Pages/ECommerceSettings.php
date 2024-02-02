<?php

declare(strict_types=1);

namespace App\FilamentTenant\Clusters\Settings\Pages;

use App\Filament\Rules\FullyQualifiedDomainNameRule;
use App\FilamentTenant\Support\Concerns\AuthorizeEcommerceSettings;
use App\Settings\ECommerceSettings as SettingsECommerceSettings;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\TextInput;

class ECommerceSettings extends TenantBaseSettings
{
    use AuthorizeEcommerceSettings;

    protected static string $settings = SettingsECommerceSettings::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $title = 'E Commerce Settings';

    protected function getFormSchema(): array
    {
        return [
            Card::make([
                TextInput::make('front_end_domain')
                    ->nullable()
                    ->rules([new FullyQualifiedDomainNameRule()])
                    ->maxLength(100)
                    ->columnSpan('full'),
            ])
                ->columns(2),
        ];
    }
}
