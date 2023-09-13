<?php

declare(strict_types=1);

namespace App\FilamentTenant\Pages\Settings;

use App\Filament\Rules\FullyQualifiedDomainNameRule;
use App\Settings\ECommerceSettings as SettingsECommerceSettings;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\TextInput;
use App\Features\ECommerce\ECommerceBase;

class ECommerceSettings extends TenantBaseSettings
{
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

    protected static function authorizeAccess(): bool
    {
        /** @var \Domain\Admin\Models\Admin $user */
        $user = auth()->user();

        return tenancy()->tenant?->features()->active(ECommerceBase::class) &&
            $user->can('ecommerceSettings.ecommerce');
    }
}
