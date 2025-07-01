<?php

declare(strict_types=1);

namespace App\FilamentTenant\Clusters\Settings\Pages;

use App\Settings\APISettings as SettingsAPISettings;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Set;
use Illuminate\Support\Str;

class APISettings extends TenantBaseSettings
{
    protected static string $settings = SettingsAPISettings::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $title = 'API Settings';

    #[\Override]
    public static function canAccess(): bool
    {
        return filament_admin()->hasRole(config()->string('domain.role.super_admin'));
    }

    #[\Override]
    protected function getFormSchema(): array
    {
        return [
            Section::make([
                TextInput::make('api_key')
                    ->nullable()
                    ->maxLength(100)
                    ->readOnly()
                    ->helperText('This is your API key. It is used to authenticate API requests. Keep it secret and do not share it publicly.')
                    ->columnSpan('full')
                    ->suffixAction(
                        Action::make('generate_api_key')
                            ->icon('heroicon-m-cog')
                            ->requiresConfirmation()
                            ->modalHeading(fn ($state) => $state ? 'Regenerate API Key' : 'Generate API Key'
                            )
                            ->modalSubmitActionLabel(fn ($state) => $state ? 'Regenerate' : 'Generate'
                            )
                            ->action(function (Set $set, $state) {
                                $state = Str::random(60); // 60-character secure random string
                                $set('api_key', $state);
                            })
                    ),

            ])
                ->columns(2),
        ];
    }
}
