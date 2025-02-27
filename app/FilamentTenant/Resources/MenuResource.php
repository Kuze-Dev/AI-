<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Features\CMS\Internationalization;
use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources\MenuResource\Pages;
use App\FilamentTenant\Support\Tree;
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
use Domain\Tenant\TenantFeatureSupport;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;

class MenuResource extends Resource
{
    protected static ?string $model = Menu::class;

    protected static ?string $navigationIcon = 'heroicon-o-bars-3';

    protected static ?string $recordTitleAttribute = 'name';

    #[\Override]
    public static function getNavigationGroup(): ?string
    {
        return trans('CMS');
    }

    #[\Override]
    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'nodes.url', 'nodes.label'];
    }

    /** @return Builder<Menu> */
    #[\Override]
    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->withCount('nodes');
    }

    /** @param  Menu  $record */
    #[\Override]
    public static function getGlobalSearchResultDetails(Model $record): array
    {

        return array_filter([
            'Total Nodes' => $record->nodes_count,
            'Selected Sites' => implode(',', $record->sites()->pluck('name')->toArray()),
        ]);
    }

    #[\Override]
    public static function resolveRecordRouteBinding(int|string $key): ?Model
    {
        return app(static::getModel())
            ->resolveRouteBindingQuery(static::getEloquentQuery(), $key, static::getRecordRouteKeyName())
            ->with('parentNodes.children')
            ->first();
    }

    #[\Override]
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->unique(
                            modifyRuleUsing: function ($livewire, Unique $rule, $state, \Filament\Forms\Get $get, $record) {
                                if (TenantFeatureSupport::active(\App\Features\CMS\SitesManagement::class)) {
                                    return false;
                                }
                                if (TenantFeatureSupport::active(Internationalization::class)) {
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
                Forms\Components\Section::make([
                    // Forms\Components\CheckboxList::make('sites')
                    \App\FilamentTenant\Support\CheckBoxList::make('sites')
                        ->required(fn () => tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class))
                        ->rules([
                            fn (?Menu $record, \Filament\Forms\Get $get) => function (string $attribute, $value, Closure $fail) use ($record, $get) {

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

                            },
                        ])
                        ->options(
                            fn () => Site::orderBy('name')
                                ->pluck('name', 'id')
                                ->toArray()
                        )
                        ->disableOptionWhen(function (string $value, Forms\Components\CheckboxList $component) {

                            $admin = filament_admin();

                            if ($admin->hasRole(config('domain.role.super_admin'))) {
                                return false;
                            }

                            $user_sites = $admin->userSite->pluck('id')->toArray();

                            $intersect = array_intersect(array_keys($component->getOptions()), $user_sites);

                            return ! in_array($value, $intersect);
                        })
                        ->formatStateUsing(fn (?Menu $record) => $record ? $record->sites->pluck('id')->toArray() : []),

                ])
                    ->hidden((bool) ! (tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class))),
                Forms\Components\Select::make('locale')
                    ->options(Locale::all()->sortByDesc('is_default')->pluck('name', 'code')->toArray())
                    ->default((string) Locale::where('is_default', true)->first()?->code)
                    ->searchable()
                    ->rules([
                        function (?Menu $record, Forms\Get $get) {

                            return function (string $attribute, $value, Closure $fail) use ($record, $get) {

                                if ($record) {
                                    $selectedLocale = $value;

                                    $originalContentId = $record->translation_id ?: $record->id;

                                    $exist = Menu::where(fn ($query) => $query->where('translation_id', $originalContentId)->orWhere('id', $originalContentId)
                                    )->where('locale', $selectedLocale)->first();

                                    if ($exist && $exist->id != $record->id) {
                                        $fail("Menu {$get('name')} has a existing ({$selectedLocale}) translation.");
                                    }
                                }

                            };
                        },
                    ])
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
                                Forms\Components\Section::make([
                                    Forms\Components\Radio::make('type')
                                        ->lazy()
                                        ->inline()
                                        ->options(
                                            collect(NodeType::cases())
                                                ->mapWithKeys(fn (NodeType $nodeType) => [$nodeType->value => Str::headline($nodeType->value)])
                                                ->toArray()
                                        ),
                                    Forms\Components\Group::make()
                                        ->visible(fn (\Filament\Forms\Get $get) => filled($get('type')))
                                        ->schema(
                                            fn (\Filament\Forms\Get $get) => match ($get('type')) {
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
                                                                    fn (string $model) =>
                                                                        /** @var class-string<\Illuminate\Database\Eloquent\Model> $model */
                                                                        [(new $model())->getMorphClass() => Str::of($model)->classBasename()->headline()]
                                                                )
                                                                ->sort()
                                                                ->toArray()
                                                        )
                                                        ->lazy(),
                                                    Forms\Components\Select::make('model_id')
                                                        ->label(
                                                            fn (\Filament\Forms\Get $get) => ($modelClass = Relation::getMorphedModel($get('model_type')))
                                                                ? (string) Str::of($modelClass)->classBasename()->headline()
                                                                : null
                                                        )
                                                        ->options(
                                                            fn (\Filament\Forms\Get $get) => ($modeClass = Relation::getMorphedModel($get('model_type')))
                                                                ? match ($modeClass) {
                                                                    ContentEntry::class => $modeClass::pluck('title', 'id')->toArray(),
                                                                    default => $modeClass::pluck('name', 'id')->toArray()
                                                                }
                                                            : null
                                                        )
                                                        ->dehydrateStateUsing(fn (string|int|null $state) => filled($state) ? (int) $state : null)
                                                        ->visible(fn (\Filament\Forms\Get $get) => filled($get('model_type'))),
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

    #[\Override]
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable()
                    ->lineClamp(1)
                    ->wrap(),
                Tables\Columns\TextColumn::make('locale')
                    ->searchable()
                    ->hidden(TenantFeatureSupport::inactive(Internationalization::class)),
                Tables\Columns\TextColumn::make('sites.name')
                    ->badge()
                    ->toggleable(condition: fn () => TenantFeatureSupport::active(\App\Features\CMS\SitesManagement::class), isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('sites')
                    ->multiple()
                    ->hidden((bool) ! (tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class)))
                    ->relationship('sites', 'name', function (Builder $query) {

                        if (filament_admin()->can('site.siteManager') &&
                        ! (filament_admin()->hasRole(config('domain.role.super_admin')))) {
                            return $query->whereIn('id', filament_admin()->userSite->pluck('id')->toArray());
                        }

                        return $query;

                    }),
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
    #[\Override]
    public static function getEloquentQuery(): Builder
    {
        if (filament_admin()->hasRole(config('domain.role.super_admin'))) {
            return static::getModel()::query();
        }

        if (TenantFeatureSupport::active(\App\Features\CMS\SitesManagement::class) &&
            filament_admin()->can('site.siteManager') &&
            ! (filament_admin()->hasRole(config('domain.role.super_admin')))
        ) {
            return static::getModel()::query()
                ->wherehas('sites', fn ($q) => $q->whereIn('site_id', filament_admin()->userSite->pluck('id')->toArray()));
        }

        return static::getModel()::query();

    }

    #[\Override]
    public static function getRelations(): array
    {
        return [
            ActivitiesRelationManager::class,
        ];
    }

    #[\Override]
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
