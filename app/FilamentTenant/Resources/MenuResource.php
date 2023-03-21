<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources\MenuResource\Pages;
use App\FilamentTenant\Support\Tree;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Closure;
use Domain\Collection\Models\Collection;
use Domain\Collection\Models\CollectionEntry;
use Domain\Menu\Enums\NodeType;
use Domain\Menu\Enums\Target;
use Domain\Menu\Models\Menu;
use Domain\Menu\Models\Node;
use Domain\Page\Models\Page;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Filters\Layout;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class MenuResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = Menu::class;

    protected static ?string $navigationGroup = 'CMS';

    protected static ?string $navigationIcon = 'heroicon-o-menu';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'nodes.url', 'nodes.label'];
    }

    /** @return Builder<Menu> */
    protected static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->withCount('nodes');
    }

    /** @param Menu $record */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [trans('Total Nodes') => $record->nodes_count];
    }

    public static function resolveRecordRouteBinding(mixed $key): ?Model
    {
        return app(static::getModel())
            ->resolveRouteBindingQuery(static::getEloquentQuery(), $key, static::getRecordRouteKeyName())
            ->with('parentNodes.children')
            ->first();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function (Closure $set, $state) {
                            $set('slug', Str::slug($state));
                        }),
                    Forms\Components\TextInput::make('slug')->required()
                        ->disabled(fn (?Menu $record) => $record !== null)
                        ->unique(ignoreRecord: true)
                        ->rules('alpha_dash')
                        ->disabled(),
                ]),
                Forms\Components\Section::make(trans('Nodes'))
                    ->schema([
                        Tree::make('nodes')
                            ->formatStateUsing(
                                fn (?Menu $record, ?array $state) => $record?->parentNodes
                                    ->mapWithKeys(self::mapNodeWithNormalizedKey(...))
                                    ->toArray() ?? $state ?? []
                            )
                            ->itemLabel(fn (array $state) => $state['label'] ?? null)
                            ->schema([
                                Forms\Components\Grid::make(['md' => 4])
                                    ->schema([
                                        Forms\Components\TextInput::make('label')
                                            ->required()
                                            ->maxLength(100)
                                            ->columnSpan(['md' => 3]),
                                        Forms\Components\Select::make('target')
                                            ->required()
                                            ->options(
                                                collect(Target::cases())
                                                    ->mapWithKeys(fn (Target $target) => [$target->value => Str::headline($target->value)])
                                                    ->toArray()
                                            )
                                            ->columnSpan(['md' => 1]),
                                    ]),
                                Forms\Components\Card::make([
                                    Forms\Components\Radio::make('type')
                                        ->lazy()
                                        ->inline()
                                        ->options(
                                            collect(NodeType::cases())
                                                ->mapWithKeys(fn (NodeType $nodeType) => [$nodeType->value => Str::headline($nodeType->value)])
                                                ->toArray()
                                        ),
                                    Forms\Components\Group::make()
                                        ->visible(fn (Closure $get) => filled($get('type')))
                                        ->schema(
                                            fn (Closure $get) => match ($get('type')) {
                                                NodeType::URL->value => [
                                                    Forms\Components\TextInput::make('url')
                                                        ->inputMode('url')
                                                        ->placeholder('https://example.com'),
                                                ],
                                                NodeType::RESOURCE->value => [
                                                    Forms\Components\Select::make('model_type')
                                                        ->label(trans('Resource'))
                                                        ->options(
                                                            collect([
                                                                Page::class,
                                                                Collection::class,
                                                                CollectionEntry::class,
                                                            ])
                                                                ->mapWithKeys(
                                                                    function (string $model) {
                                                                        /** @var class-string<\Illuminate\Database\Eloquent\Model> $model */
                                                                        return [(new $model())->getMorphClass() => Str::of($model)->classBasename()->headline()];
                                                                    }
                                                                )
                                                                ->sort()
                                                                ->toArray()
                                                        )
                                                        ->lazy(),
                                                    Forms\Components\Select::make('model_id')
                                                        ->label(
                                                            fn (Closure $get) => ($modelClass = Relation::getMorphedModel($get('model_type')))
                                                                ? (string) Str::of($modelClass)->classBasename()->headline()
                                                                : null
                                                        )
                                                        ->options(
                                                            fn (Closure $get) => ($modeClass = Relation::getMorphedModel($get('model_type')))
                                                                ? match ($modeClass) {
                                                                    CollectionEntry::class => $modeClass::pluck('title', 'id')->toArray(),
                                                                    default => $modeClass::pluck('name', 'id')->toArray()
                                                                }
                                                                : null
                                                        )
                                                        ->dehydrateStateUsing(fn (string|int|null $state) => filled($state) ? (int) $state : null)
                                                        ->visible(fn (Closure $get) => filled($get('model_type'))),
                                                ],
                                                default => []
                                            }
                                        ),
                                ]),
                            ]),
                    ])
                    ->hiddenOn('create'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->sortable(),
            ])
            ->filters([])
            ->filtersLayout(Layout::AboveContent)
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ActivitiesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenus::route('/'),
            'create' => Pages\CreateMenu::route('/create'),
            'edit' => Pages\EditMenu::route('/{record}/edit'),
        ];
    }

    private static function mapNodeWithNormalizedKey(Node $node): array
    {
        if ($node->relationLoaded('children') && $node->children->isNotEmpty()) {
            $node->setRelation('children', $node->children->mapWithKeys(self::mapNodeWithNormalizedKey(...)));
        }

        return ["record-{$node->getKey()}" => $node];
    }
}
