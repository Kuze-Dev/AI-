<?php

declare(strict_types=1);

namespace App\FilamentTenant\Pages\Settings;

use App\FilamentTenant\Support\Concerns\AuthorizeEcommerceSettings;
use App\FilamentTenant\Support\SchemaInterpolations;
use App\Settings\CustomerSettings as SettingCustomer;
use Closure;
use Domain\Blueprint\Models\Blueprint;
use Domain\Customer\Enums\CustomerEvent;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\MarkdownEditor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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
            ])->columnSpanFull(),
            Forms\Components\Section::make('Customer Notifications')
                ->schema([
                    Forms\Components\Section::make('Available Values')
                        ->schema([
                            SchemaInterpolations::make('data')
                                ->schemaData(fn (Closure $get) => Blueprint::where('id', $get('blueprint_id'))->first()?->schema),
                            Forms\Components\Placeholder::make('extra value')
                                ->content(fn () => '$customer will be available as customer array'),
                        ])
                        ->columnSpan(['md' => 1])
                        ->extraAttributes(['class' => 'md:sticky top-[5.5rem]']),
                    Forms\Components\Repeater::make('customer_email_notifications')
                        ->nullable()
                        ->schema([

                            Forms\Components\Select::make('events')
                                ->options(
                                    collect(CustomerEvent::cases())
                                        ->mapWithKeys(fn (CustomerEvent $target) => [$target->value => Str::headline($target->value)])
                                        ->toArray()
                                ),
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
                                ->columnSpanFull(),
                            MarkdownEditor::make('template')
                                ->required()
                                ->default(function (Closure $get) {

                                    $blueprint = Blueprint::whereId($get('../../blueprint_id'))->first();

                                    $customer = "{{ \$customer['first_name'] }}";

                                    if ($blueprint === null) {
                                        return '';
                                    }

                                    $interpolations = '';

                                    foreach ($blueprint->schema->sections as $section) {
                                        foreach ($section->fields as $field) {
                                            $interpolations = "{$interpolations}{$field->title}: {{ \${$section->state_name}['{$field->state_name}'] }}\n";
                                        }
                                    }

                                    return <<<markdown
                                        Hi {$customer},

                                        customer message here:

                                        {$interpolations}
                                        markdown;
                                })->columnSpanFull(),
                        ])->columnSpan(['md' => 3]),
                ])->columns(4),
            Forms\Components\Section::make(trans('Customer Import Export Settings'))
                ->schema([
                    Forms\Components\TextInput::make('date_format')
                        ->label(trans('Date Format'))
                        ->required()
                        ->helpertext('date format for validation and export and import customer the default value is default ex: format m-d-Y')
                        ->default('default'),
                ]),

        ];
    }
}
