<?php

declare(strict_types=1);

namespace App\FilamentTenant\Pages\Settings;

use App\FilamentTenant\Support\Concerns\AuthorizeEcommerceSettings;
use App\FilamentTenant\Support\SchemaInterpolations;
use App\Settings\CustomerSettings as SettingCustomer;
use Closure;
use Domain\Blueprint\Models\Blueprint;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Illuminate\Support\Facades\Auth;

class CustomerSettings extends TenantBaseSettings
{
    // use AuthorizeEcommerceSettings;

    protected static string $settings = SettingCustomer::class;

    protected static ?string $navigationIcon = 'heroicon-s-users';

    protected function getFormSchema(): array
    {
        return [
            Card::make([
                Forms\Components\Select::make('blueprint_id')
                    ->label(trans('Blueprint'))
                    ->required()
                    ->preload()
                    ->reactive()
                    ->optionsFromModel(Blueprint::class, 'name')
                    ->disabled(fn () => (app(SettingCustomer::class)->blueprint_id && Auth::user()?->id !== 1) ? true : false),
            ]),
            Card::make([
                Forms\Components\Section::make('Available Values')
                        ->schema([
                            SchemaInterpolations::make('data')
                                ->schemaData(fn (Closure $get) => Blueprint::where('id', $get('blueprint_id'))->first()?->schema),
                        ])
                        ->columnSpan(['md' => 1])
                        ->extraAttributes(['class' => 'md:sticky top-[5.5rem]']),
                    Forms\Components\Repeater::make('form_email_notifications')
                        // ->afterStateHydrated(fn (Forms\Components\Repeater $component, ?Setting $record) => $component->state($record?->formEmailNotifications->toArray() ?? []))
                        ->nullable()
                        ->schema([
                            Forms\Components\Section::make('Recipients')
                                ->schema([
                                    Forms\Components\TextInput::make('to')
                                        ->required()
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
                                    Forms\Components\TextInput::make('cc')
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
                                    Forms\Components\TextInput::make('bcc')
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
                                ])
                                ->columns(3),
                            // Forms\Components\TextInput::make('sender')
                            //     // ->default(app(FormSettings::class)->)
                            //     ->required(),
                            // Forms\Components\TextInput::make('sender_name')
                            //     ->required(),
                            Forms\Components\TextInput::make('reply_to')
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
                            Forms\Components\TextInput::make('subject')
                                ->required()
                                ->nullable()
                                ->columnSpanFull(),
                            Forms\Components\MarkdownEditor::make('template')
                                ->required()
                                // ->default(function (Closure $get) {
                                  
                                //     if ($blueprint === null) {
                                //         return '';
                                //     }

                                //     $interpolations = '';

                                //     foreach ($blueprint->schema->sections as $section) {
                                //         foreach ($section->fields as $field) {
                                //             $interpolations = "{$interpolations}{$field->title}: {{ \${$section->state_name}['{$field->state_name}'] }}\n";
                                //         }
                                //     }

                                //     return <<<markdown
                                //         Hi,

                                //         We've received a new submission:

                                //         {$interpolations}
                                //         markdown;
                                // })
                                ->columnSpanFull(),
                            Forms\Components\Toggle::make('has_attachments')
                                ->helperText('If Enabled Uploaded Files will be attach to this email notification'),
                        ])
                        ->columnSpan(['md' => 3]),
            ]),

        ];
    }
}
