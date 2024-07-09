<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources\BlueprintResource\Pages;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Closure;
use Domain\Blueprint\Actions\DeleteBlueprintAction;
use Domain\Blueprint\DataTransferObjects\FieldData;
use Domain\Blueprint\DataTransferObjects\SectionData;
use Domain\Blueprint\Enums\FieldType;
use Domain\Blueprint\Enums\ManipulationFit;
use Domain\Blueprint\Enums\ManipulationFormat;
use Domain\Blueprint\Enums\ManipulationType;
use Domain\Blueprint\Enums\MarkdownButton;
use Domain\Blueprint\Enums\RichtextButton;
use Domain\Blueprint\Models\Blueprint;
use ErrorException;
use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use HalcyonAgile\FilamentExport\Actions\ExportBulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Support\ConstraintsRelationships\Exceptions\DeleteRestrictedException;

class BlueprintResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = Blueprint::class;

    protected static ?string $navigationIcon = 'heroicon-o-table';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string
    {
        return trans('CMS');
    }

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
                        ->unique(ignoreRecord: true)
                        ->string()
                        ->maxLength(255),
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
                                    ->required()
                                    ->string()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('state_name')
                                    ->disabled(fn (?Blueprint $record, ?string $state) => (bool) ($record && Arr::first(
                                        $record->schema->sections,
                                        fn (SectionData $section) => $section->state_name === $state
                                    ))),
                                self::getFieldsSchema(),
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
                    ->searchable()
                    ->truncate('max-w-xs xl:max-w-md 2xl:max-w-2xl', true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->sortable(),
            ])

            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DeleteAction::make()
                        ->using(function (Blueprint $record) {
                            try {
                                return app(DeleteBlueprintAction::class)->execute($record);
                            } catch (DeleteRestrictedException) {
                                return false;
                            }
                        }),
                ]),

            ])
            ->bulkActions([
                ExportBulkAction::make()
                    ->queue()
                    ->query(fn (Builder $query) => $query)
                    ->mapUsing(
                        ['Id', 'Name', 'Schema'],
                        fn (Blueprint $blueprint): array => [
                            $blueprint->id,
                            $blueprint->name,
                            json_encode($blueprint->schema),
                        ]
                    )
                    ->tags([
                        'tenant:'.(tenant('id') ?? 'central'),
                    ])
                    ->withActivityLog(
                        event: 'bulk-exported',
                        description: fn (ExportBulkAction $action) => 'Bulk Exported '.$action->getModelLabel(),
                        properties: fn (ExportBulkAction $action) => ['selected_record_ids' => $action->getRecords()?->modelKeys()]
                    ),
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

    protected static function getFieldsSchema(): Forms\Components\Repeater
    {
        return Forms\Components\Repeater::make('fields')
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

                    $label .= ' ('.Str::headline($type).')';
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
                    ->string()
                    ->maxLength(255)
                    ->columnSpanFull(),
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
                            ->sort()
                            ->toArray()
                    )
                    ->required()
                    ->disabled(fn (?Blueprint $record, Closure $get) => (bool) ($record && Arr::first(
                        $record->schema->sections,
                        fn (SectionData $section) => Arr::first(
                            $section->fields,
                            fn (FieldData $field) => $field->state_name === $get('state_name'),
                        )
                    )))
                    ->afterStateUpdated(
                        fn (Forms\Components\Select $component) => $component->getContainer()
                            ->getComponent(fn (Component $component) => $component->getId() === 'field-options')
                            ?->getChildComponentContainer()
                            ->fill()
                    ),
                Forms\Components\TextInput::make('rules')
                    ->columnSpanFull()
                    ->afterStateHydrated(function (Closure $set, ?array $state): void {
                        $set('rules', implode('|', $state ?? []));
                    })
                    ->dehydrateStateUsing(function (?string $state): array {
                        return $state !== null
                            ? Str::of($state)->split('/\|/')
                                ->map(fn (string $rule) => trim($rule))
                                ->toArray()
                            : [];
                    })
                    ->helperText(new HtmlString(<<<'HTML'
                            Rules should be separated with "|". Available rules can be found on <a href="https://laravel.com/docs/validation#available-validation-rules" class="text-primary-500" target="_blank" rel="noopener noreferrer">Laravel's Documentation</a>.
                        HTML)),
                Forms\Components\TextInput::make('helper_text')
                    ->columnSpanFull(),
                Forms\Components\Section::make('Field Options')
                    ->id('field-options')
                    ->collapsible()
                    ->when(fn (Forms\Components\Section $component, array $state) => (filled($state['type'] ?? null) && count($component->getChildComponents()) > 0))
                    ->columns(['sm' => 2])
                    ->schema(fn (array $state) => self::getFieldOptionSchema(
                        $state['type'] instanceof FieldType
                            ? $state['type']
                            : FieldType::tryFrom($state['type'] ?? '')
                    )),
            ]);
    }

    protected static function getFieldOptionSchema(?FieldType $fieldType): array
    {
        return match ($fieldType) {
            FieldType::DATETIME => [
                Forms\Components\DateTimePicker::make('min')
                    ->timezone(Auth::user()?->timezone),
                Forms\Components\DateTimePicker::make('max')
                    ->timezone(Auth::user()?->timezone),
                Forms\Components\TextInput::make('format')
                    ->helperText(new HtmlString(<<<'HTML'
                            See <a href="https://www.php.net/manual/en/datetime.format.php" class="text-primary-500" target="_blank" rel="noopener noreferrer">PHP's Date/Time Format</a> for available options.
                        HTML)),
            ],
            FieldType::FILE => [
                Forms\Components\Toggle::make('multiple')
                    ->reactive(),
                Forms\Components\Toggle::make('reorder'),
                Forms\Components\Toggle::make('can_download'),
                Forms\Components\TextInput::make('accept')
                    ->afterStateHydrated(function (Closure $set, ?array $state): void {
                        $set('accept', implode(',', $state ?? []));
                    })
                    ->dehydrateStateUsing(function (?string $state): array {
                        if ($state === null) {
                            return [];
                        }

                        return Str::contains($state, ',')
                            ? Str::of($state)->split('/\,/')
                                ->map(fn (string $rule) => trim($rule))
                                ->toArray()
                            : [$state];
                    })
                    ->columnSpanFull(),
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
                    ->default(fn (Forms\Components\CheckboxList $component) => array_keys($component->getOptions()))
                    ->columns([
                        'sm' => 2,
                        'md' => 4,
                    ])
                    ->columnSpanFull(),
            ],
            FieldType::RICHTEXT => [
                Forms\Components\CheckboxList::make('buttons')
                    ->options(
                        collect(RichtextButton::cases())
                            ->mapWithKeys(fn (RichtextButton $fieldType) => [$fieldType->value => Str::headline($fieldType->value)])
                            ->toArray()
                    )
                    ->default(fn (Forms\Components\CheckboxList $component) => array_keys($component->getOptions()))
                    ->columns([
                        'sm' => 2,
                        'md' => 4,
                    ])
                    ->columnSpanFull(),
            ],
            FieldType::SELECT => [
                Forms\Components\Toggle::make('multiple')
                    ->reactive()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('min')
                    ->numeric()
                    ->integer()
                    ->dehydrateStateUsing(fn (string|int|null $state) => filled($state) ? (int) $state : null)
                    ->when(fn (Closure $get) => $get('multiple') === true),
                Forms\Components\TextInput::make('max')
                    ->numeric()
                    ->integer()
                    ->dehydrateStateUsing(fn (string|int|null $state) => filled($state) ? (int) $state : null)
                    ->when(fn (Closure $get) => $get('multiple') === true),
                Forms\Components\Repeater::make('options')
                    ->collapsible()
                    ->orderable()
                    ->itemLabel(fn (array $state) => $state['title'] ?? null)
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('value')
                            ->required(),
                        Forms\Components\TextInput::make('label')
                            ->required(),
                    ]),
            ],
            FieldType::CHECKBOX => [
                Forms\Components\Toggle::make('bulk_toggleable')
                    ->reactive()
                    ->columnSpanFull(),
                Forms\Components\Repeater::make('options')
                    ->collapsible()
                    ->orderable()
                    ->itemLabel(fn (array $state) => $state['title'] ?? null)
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('value')
                            ->required(),
                        Forms\Components\TextInput::make('label')
                            ->required(),
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
            FieldType::RELATED_RESOURCE => [
                Forms\Components\Select::make('resource')
                    ->columnSpanFull()
                    ->lazy()
                    ->afterStateUpdated(function (Closure $set) {
                        $set('relation_scopes', []);
                    })
                    ->options(
                        (new Collection(config('domain.blueprint.related_resources', [])))
                            ->keys()
                            ->mapWithKeys(
                                function (string $model) {
                                    /** @var class-string<\Illuminate\Database\Eloquent\Model> $model */
                                    return [(new $model())->getMorphClass() => Str::of($model)->classBasename()->headline()];
                                }
                            )
                            ->sort()
                            ->toArray()
                    )
                    ->reactive(),
                Forms\Components\Toggle::make('multiple')
                    ->reactive()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('min')
                    ->numeric()
                    ->integer()
                    ->dehydrateStateUsing(fn (string|int|null $state) => filled($state) ? (int) $state : null)
                    ->when(fn (Closure $get) => $get('multiple') === true),
                Forms\Components\TextInput::make('max')
                    ->numeric()
                    ->integer()
                    ->dehydrateStateUsing(fn (string|int|null $state) => filled($state) ? (int) $state : null)
                    ->when(fn (Closure $get) => $get('multiple') === true),
                Forms\Components\Group::make()
                    ->columnSpanFull()
                    ->hidden(function (Closure $get) {
                        $modelClass = Relation::getMorphedModel($get('resource'));
                        $relationScopes = config("domain.blueprint.related_resources.{$modelClass}.relation_scopes", []);

                        return count($relationScopes) <= 0;
                    })
                    ->schema(function (Closure $get) {
                        $modelClass = Relation::getMorphedModel($get('resource'));
                        $relationScopes = config("domain.blueprint.related_resources.{$modelClass}.relation_scopes", []);

                        $schema = [];

                        foreach ($relationScopes as $relationName => $options) {
                            /** @var \Illuminate\Database\Eloquent\Model $related */
                            $related = (new $modelClass())->{$relationName}()->getRelated();

                            $schema[] = Forms\Components\Select::make("relation_scopes.$relationName")
                                ->label(Str::headline($relationName))
                                ->options(
                                    $related->query()
                                        ->pluck($options['title_column'], $related->getKeyName())
                                        ->toArray()
                                );
                        }

                        return $schema;
                    }),
            ],
            FieldType::REPEATER => [
                Forms\Components\TextInput::make('min')
                    ->numeric()
                    ->integer()
                    ->dehydrateStateUsing(fn (string|int|null $state) => filled($state) ? (int) $state : null),
                Forms\Components\TextInput::make('max')
                    ->numeric()
                    ->integer()
                    ->dehydrateStateUsing(fn (string|int|null $state) => filled($state) ? (int) $state : null),
                self::getFieldsSchema()
                    ->columnSpanFull(),
            ],
            FieldType::MEDIA => [
                Forms\Components\Toggle::make('multiple')
                    ->reactive(),
                Forms\Components\Toggle::make('reorder'),
                Forms\Components\TextInput::make('accept')
                    ->afterStateHydrated(function (Closure $set, ?array $state): void {
                        $set('accept', implode(',', $state ?? []));
                    })
                    ->dehydrateStateUsing(function (?string $state): array {
                        if ($state === null) {
                            return [];
                        }

                        return Str::contains($state, ',')
                            ? Str::of($state)->split('/\,/')
                                ->map(fn (string $rule) => trim($rule))
                                ->toArray()
                            : [$state];
                    })
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('min_size')
                    ->helperText(new HtmlString(<<<'HTML'
                         in kb
                        HTML))
                    ->numeric()
                    ->integer()
                    ->dehydrateStateUsing(fn (string|int|null $state) => filled($state) ? (int) $state : null),
                Forms\Components\TextInput::make('max_size')
                    ->helperText(new HtmlString(<<<'HTML'
                         in kb
                        HTML))
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
                Forms\Components\Repeater::make('conversions')
                    ->schema([
                        Forms\Components\TextInput::make('name')->required(),
                        Forms\Components\Fieldset::make('Manipulations')
                            ->statePath('manipulations')
                            ->schema(
                                function (?Blueprint $record, $state) {

                                    $stateData = fn (ManipulationType $type) => $state[$type->value]['params'][0] ?? null;
                                    $FormatOptions = [];
                                    foreach (ManipulationFormat::cases() as $format) {
                                        $FormatOptions[$format->value] = $format->value;
                                    }

                                    $Fitoptions = [];
                                    foreach (ManipulationFit::cases() as $format) {
                                        $Fitoptions[$format->value] = $format->value;
                                    }

                                    return collect(ManipulationType::cases())
                                        ->map(fn (ManipulationType $manipulationType) => match ($manipulationType) {
                                            ManipulationType::WIDTH, ManipulationType::HEIGHT => Forms\Components\TextInput::make(
                                                $manipulationType->value
                                            )
                                                ->translateLabel()
                                                ->numeric()
                                                ->minValue(0)
                                                ->required()
                                                ->formatStateUsing(fn () => $stateData($manipulationType)),
                                            ManipulationType::TYPE => Forms\Components\Group::make()->schema([
                                                Forms\Components\Select::make('type')
                                                    ->translateLabel()
                                                    ->options($FormatOptions)
                                                    ->formatStateUsing(fn () => $stateData($manipulationType)),
                                            ]),

                                            ManipulationType::FIT => Forms\Components\Group::make()->schema([
                                                Forms\Components\Select::make('fit')
                                                    ->translateLabel()
                                                    ->required()
                                                    ->options($Fitoptions)
                                                    ->formatStateUsing(fn () => $stateData($manipulationType))
                                                    ->hint(
                                                        Str::of('[Documentation](https://spatie.be/docs/image/v1/image-manipulations/resizing-images)')
                                                            ->inlineMarkdown()
                                                            ->toHtmlString()
                                                    )
                                                    ->hintColor('primary')
                                                    ->hintIcon('heroicon-s-question-mark-circle'),
                                            ]),
                                            /** @phpstan-ignore-next-line */
                                            default => throw new ErrorException(
                                                ManipulationType::class.'::'.Str::upper($manipulationType->value).' field not setup for conversion manipulation.'
                                            )
                                        })
                                        ->toArray();
                                }
                            )
                            ->columns(2),
                    ])
                    ->columnSpanFull(),

            ],
            FieldType::RADIO => [
                Forms\Components\Toggle::make('inline')
                    ->columnSpanFull(),
                Forms\Components\Repeater::make('options')
                    ->collapsible()
                    ->orderable()
                    ->itemLabel(fn (array $state) => $state['title'] ?? null)
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('value')
                            ->required(),
                        Forms\Components\TextInput::make('label')
                            ->required(),
                    ]),
                Forms\Components\Repeater::make('descriptions')
                    ->collapsible()
                    ->orderable()
                    ->itemLabel(fn (array $state) => $state['title'] ?? null)
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('value'),
                        Forms\Components\TextInput::make('description'),
                    ]),
            ],
            FieldType::TINYEDITOR => [
                Forms\Components\TextInput::make('min_length')
                    ->numeric()
                    ->integer()
                    ->dehydrateStateUsing(fn (string|int|null $state) => filled($state) ? (int) $state : null),
                Forms\Components\TextInput::make('max_length')
                    ->numeric()
                    ->integer()
                    ->dehydrateStateUsing(fn (string|int|null $state) => filled($state) ? (int) $state : null),
            ],
            default => [],
        };
    }
}
