<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources\MenuResource\Pages;
use App\FilamentTenant\Support\Tree;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Closure;
use Domain\Content\Models\Content;
use Domain\Content\Models\ContentEntry;
use Domain\Internationalization\Models\Locale;
use Domain\Menu\Enums\NodeType;
use Domain\Menu\Enums\Target;
use Domain\Menu\Models\Menu;
use Domain\Menu\Models\Node;
use Domain\Page\Models\Page;
use Domain\Site\Models\Site;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;

class MenuResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = Menu::class;

    protected static ?string $navigationIcon = 'heroicon-o-menu';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string
    {
        return trans('CMS');
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'nodes.url', 'nodes.label'];
    }

    /** @return Builder<Menu> */
    protected static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->withCount('nodes');
    }

    /** @param  Menu  $record */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        /** @phpstan-ignore-next-line */
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
                        ->unique(
                            callback: function ($livewire, Unique $rule, $state, Closure $get, $record) {
                                if (tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class)) {
                                    return false;
                                }
                                if (tenancy()->tenant?->features()->active(\App\Features\CMS\Internationalization::class)) {
                                    $exist = Menu::whereName($state)->whereLocale($get('locale'))->whereNot('id', $record?->id)->count();
                                    if (! $exist) {
                                        return false;
                                    }
                                }

                                return $rule;
                            },
                            ignoreRecord: true
                        )
                        ->string()
                        ->maxLength(255),
                ]),
                Forms\Components\Card::make([
                    Forms\Components\CheckboxList::make('sites')
                        ->required(fn () => tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class))
                        ->rules([
                            function (?Menu $record, Closure $get) {

                                return function (string $attribute, $value, Closure $fail) use ($record, $get) {

                                    $siteIDs = $value;

                                    if ($record) {
                                        $siteIDs = array_diff($siteIDs, $record->sites->pluck('id')->toArray());

                                        $menu = Menu::where('name', $get('name'))
                                            ->where('id', '!=', $record->id)
                                            ->whereHas(
                                                'sites',
                                                fn ($query) => $query->whereIn('site_id', $siteIDs)
                                            )->count();
                                    } else {
                                        $menu = Menu::where('name', $get('name'))->whereHas(
                                            'sites',
                                            fn ($query) => $query->whereIn('site_id', $siteIDs)
                                        )->count();

                                    }

                                    if ($menu > 0) {
                                        $fail("Menu {$get('name')} is already available in selected sites.");
                                    }

                                };
                            },
                        ])
                        ->options(
                            fn () => Site::orderBy('name')
                                ->pluck('name', 'id')
                                ->toArray()
                        )
                        ->formatStateUsing(fn (?Menu $record) => $record ? $record->sites->pluck('id')->toArray() : []),

                ])
                    ->hidden((bool) ! (tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class) && Auth::user()?->hasRole(config('domain.role.super_admin')))),
                Forms\Components\Select::make('locale')
                    ->options(Locale::all()->sortByDesc('is_default')->pluck('name', 'code')->toArray())
                    ->default((string) Locale::where('is_default', true)->first()?->code)
                    ->searchable()
                    ->hidden((bool) tenancy()->tenant?->features()->inactive(\App\Features\CMS\Internationalization::class))
                    ->required(),
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
                                                                Content::class,
                                                                ContentEntry::class,
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
                                                                    ContentEntry::class => $modeClass::pluck('title', 'id')->toArray(),
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
                    ->searchable()
                    ->truncate('max-w-xs lg:max-w-md 2xl:max-w-3xl', true),
                Tables\Columns\TextColumn::make('locale')
                    ->searchable()
                    ->hidden((bool) tenancy()->tenant?->features()->inactive(\App\Features\CMS\Internationalization::class)),
                Tables\Columns\TagsColumn::make('sites.name')
                    ->toggleable(condition: function () {
                        return tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class);
                    }, isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('sites')
                    ->multiple()
                    ->hidden((bool) ! (tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class)))
                    ->relationship('sites', 'name'),
            ])

            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    /** @return Builder<\Domain\Menu\Models\Menu> */
    public static function getEloquentQuery(): Builder
    {
        if (Auth::user()?->hasRole(config('domain.role.super_admin'))) {
            return static::getModel()::query();
        }

        if (tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class) &&
            Auth::user()?->can('site.siteManager') &&
            ! (Auth::user()->hasRole(config('domain.role.super_admin')))
        ) {
            return static::getModel()::query()->wherehas('sites', function ($q) {
                return $q->whereIn('site_id', Auth::user()?->userSite->pluck('id')->toArray());
            });
        }

        return static::getModel()::query();

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
