<?php

declare(strict_types=1);

namespace App\FilamentTenant\Pages\Settings;

use App\Settings\OrderSettings as SettingsOrderSettings;
use Filament\Forms;
use Illuminate\Support\Str;
use App\Features\ECommerce\ECommerceBase;

class OrderSettings extends TenantBaseSettings
{
    protected static string $settings = SettingsOrderSettings::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $title = 'Order Settings';

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Section::make('Email')
                ->schema([
                    Forms\Components\TextInput::make('email_sender_name')
                        ->label(trans('Sender Name'))
                        ->required(),
                    Forms\Components\TextInput::make('email_reply_to')
                        ->label(trans('Reply To'))
                        ->helperText('Seperated by comma')
                        ->nullable()
                        ->afterStateHydrated(function (Forms\Components\TextInput $component, ?array $state): void {
                            $component->state(implode(',', $state ?? []));
                        })
                        ->dehydrateStateUsing(fn (string|array|null $state) => is_string($state)
                            ? Str::of($state)
                                ->split('/\,/')
                                ->map(fn (string $rule) => trim($rule))
                                ->toArray()
                            : ($state ?? [])),
                    Forms\Components\RichEditor::make('email_footer')
                        ->label(trans('Email Footer'))
                        ->disableToolbarButtons([
                            'redo',
                            'undo',
                            'attachFiles',
                        ])
                        ->helperText('This will be automatically centered in the email layout.')
                        ->columnSpanFull(),
                ]),
        ];
    }

    protected static function authorizeAccess(): bool
    {
        /** @var \Domain\Admin\Models\Admin $user */
        $user = auth()->user();

        return tenancy()->tenant?->features()->active(ECommerceBase::class) &&
            $user->can('ecommerceSettings.order');
    }
}
