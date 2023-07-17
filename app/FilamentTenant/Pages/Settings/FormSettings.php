<?php

declare(strict_types=1);

namespace App\FilamentTenant\Pages\Settings;

use App\Settings\FormSettings as SettingsFormSettings;
use Closure;
use Filament\Forms;
use Support\Captcha\CaptchaProvider;

class FormSettings extends TenantBaseSettings
{
    protected static string $settings = SettingsFormSettings::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $title = 'Form Settings';

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Section::make(trans('Captcha'))
                ->schema([
                    Forms\Components\TextInput::make('sender_email')
                        ->label('Sender Email')
                        ->placeholder('Email')
                        ->required(),
                    Forms\Components\Select::make('provider')
                        ->options([
                            CaptchaProvider::GOOGLE_RECAPTCHA->value => 'Google reCAPTCHA',
                            CaptchaProvider::CLOUDFLARE_TURNSTILE->value => 'Cloudflare Turnstile',
                        ])
                        ->enum(CaptchaProvider::class)
                        ->dehydrateStateUsing(
                            fn(CaptchaProvider|string|null $state) => is_string($state)
                                ? CaptchaProvider::tryFrom($state)
                                : $state
                        )
                        ->lazy(),
                    Forms\Components\TextInput::make('site_key')
                        ->required()
                        ->visible(fn(Closure $get) => filled($get('provider'))),
                    Forms\Components\TextInput::make('secret_key')
                        ->required()
                        ->visible(fn(Closure $get) => filled($get('provider'))),
                ]),
        ];
    }
}
