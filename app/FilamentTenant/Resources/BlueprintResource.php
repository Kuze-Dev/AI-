<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources\BlueprintResource\Pages;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Closure;
use Domain\Blueprint\DataTransferObjects\FieldData;
use Domain\Blueprint\DataTransferObjects\SectionData;
use Domain\Blueprint\Enums\FieldType;
use Domain\Blueprint\Enums\MarkdownButton;
use Domain\Blueprint\Enums\RichtextButton;
use Domain\Blueprint\Models\Blueprint;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\HtmlString;

class BlueprintResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = Blueprint::class;

    protected static ?string $navigationGroup = 'CMS';

    protected static ?string $navigationIcon = 'heroicon-o-table';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->unique(ignoreRecord: true),
                ]),
                Forms\Components\Card::make()
                    ->statePath('schema')
                    ->schema([
                        Forms\Components\Repeater::make('sections')
                            ->orderable()
                            ->itemLabel(fn (array $state) => $state['title'] ?? null)
                            ->minItems(1)
                            ->collapsible()
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->lazy()
                                    ->required(),
                                Forms\Components\TextInput::make('state_name')
                                    ->disabled(fn (?Blueprint $record, ?string $state) => (bool) ($record && Arr::first(
                                        $record->schema->sections,
                                        fn (SectionData $section) => $section->state_name === $state
                                    ))),
                                Forms\Components\Repeater::make('fields')
                                    ->orderable()
                                    ->itemLabel(function (array $state) {
                                        if (blank($state['title'])) {
                                            return null;
                                        }

                                        $label = $state['title'];

                                        if (filled($state['type'])) {
                                            $type = $state['type'] instanceof FieldType
                                                ? $state['type']->value
                                                : $state['type'];

                                            $label .= ' (' . ucfirst($type) . ')';
                                        }

                                        return $label;
                                    })
                                    ->minItems(1)
                                    ->columns(['sm' => 3])
                                    ->collapsible()
                                    ->schema([
                                        Forms\Components\TextInput::make('title')
                                            ->required()
                                            ->lazy()
                                            ->columnSpan(['sm' => 3]),
                                        Forms\Components\TextInput::make('state_name')
                                            ->columnSpan(['sm' => 2])
                                            ->disabled(fn (?Blueprint $record, ?string $state) => (bool) ($record && Arr::first(
                                                $record->schema->sections,
                                                fn (SectionData $section) => Arr::first(
                                                    $section->fields,
                                                    fn (FieldData $field) => $field->state_name === $state,
                                                )
                                            ))),
                                        Forms\Components\Select::make('type')
                                            ->reactive()
                                            ->options(
                                                collect(FieldType::cases())
                                                    ->mapWithKeys(fn (FieldType $fieldType) => [$fieldType->value => Str::headline($fieldType->value)])
                                                    ->toArray()
                                            )
                                            ->required()
                                            ->disabled(fn (?Blueprint $record, Closure $get) => (bool) ($record && Arr::first(
                                                $record->schema->sections,
                                                fn (SectionData $section) => Arr::first(
                                                    $section->fields,
                                                    fn (FieldData $field) => $field->state_name === $get('state_name'),
                                                )
                                            ))),
                                        Forms\Components\TextInput::make('rules')
                                            ->columnSpan(['sm' => 3])
                                            ->afterStateHydrated(function (Closure $set, ?array $state): void {
                                                $set('rules', implode('|', $state ?? []));
                                            })
                                            ->dehydrateStateUsing(function (string|null $state): array {
                                                if ($state === null) {
                                                    return [];
                                                }

                                                return Str::contains($state, '|')
                                                    ? Str::of($state)->split('/\|/')
                                                        ->map(fn (string $rule) => trim($rule))
                                                        ->toArray()
                                                    : [$state];
                                            })
                                            ->helperText(new HtmlString(<<<HTML
                                                    Rules should be separated with "|". Available rules can be found on <a href="https://laravel.com/docs/validation" class="text-primary-500" target="_blank" rel="noopener noreferrer">Laravel's Documentation</a>.
                                                HTML)),
                                        Forms\Components\Section::make('Field Options')
                                            ->collapsible()
                                            ->when(fn (array $state) => filled($state['type'] ?? null))
                                            ->columns(['sm' => 2])
                                            ->schema(fn (array $state) => match ($state['type'] instanceof FieldType ? $state['type'] : FieldType::tryFrom($state['type'] ?? '')) {
                                                FieldType::DATETIME => [
                                                    Forms\Components\DateTimePicker::make('min')
                                                        ->timezone(Auth::user()?->timezone),
                                                    Forms\Components\DateTimePicker::make('max')
                                                        ->timezone(Auth::user()?->timezone),
                                                    Forms\Components\TextInput::make('format'),
                                                ],
                                                FieldType::FILE => [
                                                    Forms\Components\Toggle::make('multiple')
                                                        ->reactive(),
                                                    Forms\Components\Toggle::make('reorder'),
                                                    Forms\Components\TextInput::make('accept')
                                                        ->columnSpan(2),
                                                    Forms\Components\TextInput::make('min_size')
                                                        ->numeric()
                                                        ->integer()
                                                        ->dehydrateStateUsing(fn (string|int|null $state) => filled($state) ? (int) $state : null),
                                                    Forms\Components\TextInput::make('max_size')
                                                        ->numeric()
                                                        ->integer()
                                                        ->dehydrateStateUsing(fn (string|int|null $state) => filled($state) ? (int) $state : null),
                                                    Forms\Components\TextInput::make('min_files')
                                                        ->numeric()
                                                        ->integer()
                                                        ->when(fn (Closure $get) => $get('multiple') === true)
                                                        ->dehydrateStateUsing(fn (string|int|null $state) => filled($state) ? (int) $state : null),
                                                    Forms\Components\TextInput::make('max_files')
                                                        ->numeric()
                                                        ->integer()
                                                        ->when(fn (Closure $get) => $get('multiple') === true)
                                                        ->dehydrateStateUsing(fn (string|int|null $state) => filled($state) ? (int) $state : null),
                                                ],
                                                FieldType::MARKDOWN => [
                                                    Forms\Components\CheckboxList::make('buttons')
                                                        ->options(
                                                            collect(MarkdownButton::cases())
                                                                ->mapWithKeys(fn (MarkdownButton $fieldType) => [$fieldType->value => Str::headline($fieldType->value)])
                                                                ->toArray()
                                                        )
                                                        ->lazy()
                                                        ->default(fn (Forms\Components\CheckboxList $component) => array_keys($component->getOptions()))
                                                        ->afterStateUpdated(fn (Closure $set, string|array|null $state) => $set('buttons', $state === null ? [] : Arr::wrap($state)))
                                                        ->columns([
                                                            'sm' => 2,
                                                            'md' => 4,
                                                        ])
                                                        ->columnSpan(['sm' => 2]),
                                                ],
                                                FieldType::RICHTEXT => [
                                                    Forms\Components\CheckboxList::make('buttons')
                                                        ->options(
                                                            collect(RichtextButton::cases())
                                                                ->mapWithKeys(fn (RichtextButton $fieldType) => [$fieldType->value => Str::headline($fieldType->value)])
                                                                ->toArray()
                                                        )
                                                        ->lazy()
                                                        ->default(fn (Forms\Components\CheckboxList $component) => array_keys($component->getOptions()))
                                                        ->afterStateUpdated(fn (Closure $set, string|array|null $state) => $set('buttons', $state === null ? [] : Arr::wrap($state)))
                                                        ->columns([
                                                            'sm' => 2,
                                                            'md' => 4,
                                                        ])
                                                        ->columnSpan(['sm' => 2]),
                                                ],
                                                FieldType::SELECT => [
                                                    Forms\Components\Toggle::make('multiple'),
                                                    Forms\Components\Repeater::make('options')
                                                        ->collapsible()
                                                        ->orderable()
                                                        ->itemLabel(fn (array $state) => $state['title'] ?? null)
                                                        ->columnSpan(['sm' => 2])
                                                        ->columns(2)
                                                        ->schema([
                                                            Forms\Components\TextInput::make('value'),
                                                            Forms\Components\TextInput::make('label'),
                                                        ]),
                                                ],
                                                FieldType::TEXTAREA => [
                                                    Forms\Components\TextInput::make('min_length')
                                                        ->numeric()
                                                        ->integer()
                                                        ->dehydrateStateUsing(fn (string|int|null $state) => filled($state) ? (int) $state : null),
                                                    Forms\Components\TextInput::make('max_length')
                                                        ->numeric()
                                                        ->integer()
                                                        ->dehydrateStateUsing(fn (string|int|null $state) => filled($state) ? (int) $state : null),
                                                    Forms\Components\TextInput::make('rows')
                                                        ->numeric()
                                                        ->integer()
                                                        ->dehydrateStateUsing(fn (string|int|null $state) => filled($state) ? (int) $state : null),
                                                    Forms\Components\TextInput::make('cols')
                                                        ->numeric()
                                                        ->integer()
                                                        ->dehydrateStateUsing(fn (string|int|null $state) => filled($state) ? (int) $state : null),
                                                ],
                                                FieldType::TEXT,
                                                FieldType::EMAIL,
                                                FieldType::TEL,
                                                FieldType::URL,
                                                FieldType::PASSWORD => [
                                                    Forms\Components\TextInput::make('min_length')
                                                        ->numeric()
                                                        ->integer()
                                                        ->dehydrateStateUsing(fn (string|int|null $state) => filled($state) ? (int) $state : null),
                                                    Forms\Components\TextInput::make('max_length')
                                                        ->numeric()
                                                        ->integer()
                                                        ->dehydrateStateUsing(fn (string|int|null $state) => filled($state) ? (int) $state : null),
                                                ],
                                                FieldType::NUMBER => [
                                                    Forms\Components\TextInput::make('min')
                                                        ->numeric()
                                                        ->integer()
                                                        ->dehydrateStateUsing(fn (string|int|null $state) => filled($state) ? (int) $state : null),
                                                    Forms\Components\TextInput::make('max')
                                                        ->numeric()
                                                        ->integer()
                                                        ->dehydrateStateUsing(fn (string|int|null $state) => filled($state) ? (int) $state : null),
                                                    Forms\Components\TextInput::make('step')
                                                        ->numeric()
                                                        ->integer()
                                                        ->dehydrateStateUsing(fn (string|int|null $state) => filled($state) ? (int) $state : null),
                                                ],
                                                FieldType::TOGGLE => [],
                                                default => [],
                                            }),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBlueprints::route('/'),
            'create' => Pages\CreateBlueprint::route('/create'),
            'edit' => Pages\EditBlueprint::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            ActivitiesRelationManager::class,
        ];
    }
}
