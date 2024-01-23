<?php

declare(strict_types=1);

namespace App\FilamentTenant\Pages\Settings;

use App\Features\Service\ServiceBase;
use App\Settings\ServiceSettings as ServiceCategorySettings;
use Closure;
use Domain\Taxonomy\Models\Taxonomy;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Str;

class ServiceSettings extends TenantBaseSettings
{
    protected static string $settings = ServiceCategorySettings::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $title = 'Service Settings';

    public static function authorizeAccess(): bool
    {
        return parent::authorizeAccess() && tenancy()->tenant?->features()->active(ServiceBase::class);
    }

    protected function getFormSchema(): array
    {
        return [
            Card::make([
                Select::make('service_category')
                    ->placeholder(trans('Select Category'))
                    ->options(Taxonomy::pluck('name', 'id'))
                    ->columnSpan('full'),
                Forms\Components\TextInput::make('domain_path_segment')
                    ->label(trans('Domain Path Segment'))
                    ->required()
                    ->dehydrateStateUsing(fn (?string $state) => is_null($state) ? '' : $state),
            ]),
            Forms\Components\Card::make([
                Forms\Components\Section::make('Admin Email Notification')
                    ->schema([
                        Forms\Components\Toggle::make('admin_should_receive')
                            ->inline(false)
                            ->label('')
                            ->helperText('Enable this if you want to receive emails whenever a service order status changes.')
                            ->reactive(),
                        Forms\Components\TextInput::make('admin_main_receiver')
                            ->label(trans('Main Receiver'))
                            ->required(function (\Filament\Forms\Get $get) {
                                return $get('admin_should_receive');
                            })
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
            Section::make('Service Order Section')
                ->schema([
                    TextInput::make('days_before_due_date_notification')
                        ->placeholder(trans('Days Before Due Date Notification'))
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(31)
                        ->columnSpan('full'),
                ]),
        ];
    }
}
