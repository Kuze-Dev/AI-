<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources\FormResource\Pages;
use App\FilamentTenant\Resources\FormResource\RelationManagers\FormSubmissionsRelationManager;
use App\FilamentTenant\Support\SchemaInterpolations;
use App\Settings\FormSettings;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Domain\Blueprint\Models\Blueprint;
use Domain\Form\Models\Form as FormModel;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\Facades\Auth;
use Exception;
use Filament\Resources\RelationManagers\RelationGroup;
use Illuminate\Support\Str;
use Closure;

class FormResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = FormModel::class;

    protected static ?string $navigationGroup = 'CMS';

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make([
                    Forms\Components\TextInput::make('name')
                        ->unique(ignoreRecord: true)
                        ->required(),
                    Forms\Components\Select::make('blueprint_id')
                        ->label(trans('Blueprint'))
                        ->required()
                        ->preload()
                        ->optionsFromModel(Blueprint::class, 'name')
                        ->disabled(fn (?FormModel $record) => $record !== null)
                        ->reactive(),
                    Forms\Components\Toggle::make('store_submission'),
                    Forms\Components\Toggle::make('uses_captcha')
                        ->disabled(fn (FormSettings $formSettings) => ! $formSettings->provider)
                        ->helperText(
                            fn (FormSettings $formSettings) => ! $formSettings->provider
                                ? trans('Currently unavailable. Please setup Captcha(in Settings > Form Settings) first.')
                                : null
                        ),
                ]),
                Forms\Components\Card::make([
                    Forms\Components\Section::make('Available Values')
                        ->schema([
                            SchemaInterpolations::make('data')
                                ->schemaData(fn (Closure $get) => Blueprint::where('id', $get('blueprint_id'))->first()?->schema),
                        ])
                        ->columnSpan(['md' => 1])
                        ->extraAttributes(['class' => 'md:sticky top-[5.5rem]']),
                    Forms\Components\Repeater::make('form_email_notifications')
                        ->afterStateHydrated(fn (Forms\Components\Repeater $component, ?FormModel $record) => $component->state($record?->formEmailNotifications->toArray() ?? []))
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
                            Forms\Components\TextInput::make('sender')
                                ->required(),
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
                                ->default(function (Closure $get) {
                                    $blueprint = Blueprint::whereId($get('../../blueprint_id'))->first();

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
                                        Hi,

                                        We've received a new submission:

                                        {$interpolations}
                                        markdown;
                                })
                                ->columnSpanFull(),
                        ])
                        ->columnSpan(['md' => 3]),
                ])->columns(4),
            ]);
    }

    /** @throws Exception */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('form_submissions_count')
                    ->counts('formSubmissions')
                    ->formatStateUsing(fn (FormModel $record, ?int $state) => $record->store_submission ? $state : 'N/A')
                    ->icon('heroicon-s-mail')
                    ->color(fn (FormModel $record) => $record->store_submission ? 'success' : 'secondary'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->sortable(),
            ])
            ->filters([])

            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationGroup::make('Main', [
                FormSubmissionsRelationManager::class,
                ActivitiesRelationManager::class,
            ]),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListForms::route('/'),
            'create' => Pages\CreateForm::route('/create'),
            'edit' => Pages\EditForm::route('/{record}/edit'),
        ];
    }
}
