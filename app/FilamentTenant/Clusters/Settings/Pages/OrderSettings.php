<?php

declare(strict_types=1);

namespace App\FilamentTenant\Clusters\Settings\Pages;

use App\FilamentTenant\Support\Concerns\AuthorizeEcommerceSettings;
use App\Settings\OrderSettings as SettingsOrderSettings;
use Filament\Forms;
use Illuminate\Support\Str;

class OrderSettings extends TenantBaseSettings
{
    use AuthorizeEcommerceSettings;

    protected static string $settings = SettingsOrderSettings::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $title = 'Order Settings';

    #[\Override]
    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Card::make([
                Forms\Components\Section::make('Admin Email Notification')
                    ->schema([
                        Forms\Components\Toggle::make('admin_should_receive')
                            ->inline(false)
                            ->label('')
                            ->helperText('Enable this if you want to receive emails whenever an order is placed.')
                            ->reactive(),
                        Forms\Components\TextInput::make('admin_main_receiver')
                            ->label(trans('Main Receiver'))
                            ->required(fn (\Filament\Forms\Get $get) => $get('admin_should_receive'))
                            ->dehydrateStateUsing(fn (?string $state) => is_null($state) ? '' : $state),
                        Forms\Components\TextInput::make('admin_cc')
                            ->label(trans('CC'))
                            ->nullable()
                            ->helperText('Seperated by comma')
                            ->afterStateHydrated(function (Forms\Components\TextInput $component, ?array $state): void {
                                $component->state(implode(',', $state ?? []));
                            })
                            ->dehydrateStateUsing(fn (string|array|null $state) => is_string($state)
                                ? Str::of($state)
                                    ->split('/\,/')
                                    ->map(fn (string $rule) => trim($rule))
                                    ->toArray()
                                : ($state ?? [])),
                        Forms\Components\TextInput::make('admin_bcc')
                            ->label(trans('BCC'))
                            ->nullable()
                            ->helperText('Seperated by comma')
                            ->afterStateHydrated(function (Forms\Components\TextInput $component, ?array $state): void {
                                $component->state(implode(',', $state ?? []));
                            })
                            ->dehydrateStateUsing(fn (string|array|null $state) => is_string($state)
                                ? Str::of($state)
                                    ->split('/\,/')
                                    ->map(fn (string $rule) => trim($rule))
                                    ->toArray()
                                : ($state ?? [])),
                    ]),

                Forms\Components\Section::make('Customer Email Notification')
                    ->schema([
                        Forms\Components\TextInput::make('email_sender_name')
                            ->label(trans('Email Sender'))
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
            ]),
        ];
    }
}
