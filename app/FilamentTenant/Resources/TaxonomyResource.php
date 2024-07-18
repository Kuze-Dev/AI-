<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Features\CMS\SitesManagement;
use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources;
use App\FilamentTenant\Support\RouteUrlFieldset;
use App\FilamentTenant\Support\SchemaFormBuilder;
use App\FilamentTenant\Support\Tree;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Closure;
use Domain\Blueprint\Models\Blueprint;
use Domain\Internationalization\Models\Locale;
use Domain\Site\Models\Site;
use Domain\Taxonomy\Actions\DeleteTaxonomyAction;
use Domain\Taxonomy\Models\Taxonomy;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Support\ConstraintsRelationships\Exceptions\DeleteRestrictedException;
use Support\RouteUrl\Rules\MicroSiteUniqueRouteUrlRule;
use Support\RouteUrl\Rules\UniqueActiveRouteUrlRule;

class TaxonomyResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = Taxonomy::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string
    {
        return trans('CMS');
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'taxonomyTerms.name'];
    }

    /** @param  Taxonomy  $record */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        /** @phpstan-ignore-next-line */
        return [trans('Total terms') => $record->taxonomy_terms_count];
    }

    /** @return Builder<Taxonomy> */
    protected static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->withCount('taxonomyTerms');
    }

    public static function resolveRecordRouteBinding(mixed $key): ?Model
    {
        return app(static::getModel())
            ->resolveRouteBindingQuery(static::getEloquentQuery(), $key, static::getRecordRouteKeyName())
            ->with('parentTerms.children')
            ->first();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->string()
                        ->reactive()
                        ->maxLength(255)
                        ->afterStateUpdated(function (Forms\Components\TextInput $component, $livewire) {
                            $component->getContainer()
                                ->getComponent(fn (Component $component) => $component->getId() === 'route_url')
                                ?->dispatchEvent('route_url::update');
                        })
                        ->unique(ignoreRecord: true),
                    Forms\Components\Select::make('blueprint_id')
                        ->label(trans('Blueprint'))
                        ->required()
                        ->preload()
                        ->optionsFromModel(Blueprint::class, 'name')
                        ->disabled(fn (?Taxonomy $record) => $record !== null),
                    Forms\Components\Toggle::make('has_route')
                        ->reactive()
                        ->lazy()
                        ->afterStateUpdated(function (Forms\Components\Toggle $component, $state) {
                            if ($state) {
                                $component->getContainer()
                                    ->getComponent(fn (Component $component) => $component->getId() === 'route_url')
                                    ?->dispatchEvent('route_url::update');
                            }

                        })
                        ->formatStateUsing(fn (?Taxonomy $record) => $record?->activeRouteUrl ? true : false)
                        ->label(trans('Has Route')),
                    RouteUrlFieldset::make()
                        ->disabled(fn (Closure $get) => ! $get('has_route'))
                        ->hidden(fn (Closure $get) => ! $get('has_route')),
                ]),
                Forms\Components\Select::make('locale')
                    ->options(Locale::all()->sortByDesc('is_default')->pluck('name', 'code')->toArray())
                    ->default((string) Locale::where('is_default', true)->first()?->code)
                    ->searchable()
                    ->hidden((bool) tenancy()->tenant?->features()->inactive(\App\Features\CMS\Internationalization::class))
                    ->required(),

                Forms\Components\Card::make([
                    Forms\Components\CheckboxList::make('sites')
                        ->reactive()
                        ->required(fn () => tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class))
                        ->rule(fn (?Taxonomy $record, Closure $get) => new MicroSiteUniqueRouteUrlRule($record, $get('route_url')))
                        ->options(function () {

                            if (Auth::user()?->hasRole(config('domain.role.super_admin'))) {
                                return Site::orderBy('name')
                                    ->pluck('name', 'id')
                                    ->toArray();
                            }

                            return Site::orderBy('name')
                                ->whereHas('siteManager', fn ($query) => $query->where('admin_id', Auth::user()?->id))
                                ->pluck('name', 'id')
                                ->toArray();
                        })
                        ->afterStateHydrated(function (Forms\Components\CheckboxList $component, ?Taxonomy $record): void {
                            if (! $record) {
                                $component->state([]);

                                return;
                            }

                            $component->state(
                                $record->sites->pluck('id')
                                    ->intersect(array_keys($component->getOptions()))
                                    ->values()
                                    ->toArray()
                            );
                        }),
                ])
                    ->hidden((bool) tenancy()->tenant?->features()->inactive(\App\Features\CMS\SitesManagement::class)),

                Forms\Components\Section::make(trans('Terms'))->schema([
                    Tree::make('terms')
                        ->formatStateUsing(
                            fn (?Taxonomy $record, ?array $state) => $record?->parentTerms
                                ->mapWithKeys(self::mapTermWithNormalizedKey(...))
                                ->toArray() ?? $state ?? []
                        )
                        ->itemLabel(fn (array $state) => $state['name'] ?? null)
                        ->schema([
                            Forms\Components\Grid::make(['md' => 1])
                                ->schema([
                                    Forms\Components\Section::make('Term')
                                        ->schema([
                                            Forms\Components\Hidden::make('id'),
                                            Forms\Components\TextInput::make('name')
                                                ->required()
                                                ->reactive()
                                                ->lazy()
                                                ->afterStateUpdated(function (Closure $set, $state, $livewire) {
                                                    $set('url', $livewire->data['route_url']['url'].'/'.Str::of($state)->slug());

                                                    return $state;
                                                })
                                                ->unique(ignoreRecord: true),
                                            Forms\Components\Group::make([
                                                Forms\Components\Toggle::make('is_custom')
                                                    ->formatStateUsing(function (Closure $get, $state) {

                                                        if ($state) {
                                                            return $state;
                                                        }
                                                        /** @var TaxonomyTerm|null */
                                                        $term = TaxonomyTerm::with(
                                                            'routeUrls'
                                                        )->find($get('id'));

                                                        return $term ? $term->routeUrls?->is_override : $state;

                                                    })
                                                    ->label(trans('Is Custom URL'))
                                                    ->reactive(),
                                                Forms\Components\TextInput::make('url')
                                                    ->label(trans('URL'))
                                                    ->reactive()
                                                    // ->unique(ignoreRecord: true)
                                                    ->disabled(fn ($livewire, Closure $get) => ! ($livewire->data['has_route'] && $get('is_custom')))
                                                    ->hidden(fn ($livewire) => ! $livewire->data['has_route'])
                                                    ->formatStateUsing(function (Closure $get, $state, $livewire) {

                                                        if ($state) {
                                                            return $state;
                                                        }

                                                        $term = TaxonomyTerm::with(
                                                            'routeUrls'
                                                        )->find($get('id'));

                                                        return $term ? $term->ActiveRouteurl?->url : $state;

                                                    })
                                                    ->required()
                                                    ->string()
                                                    ->maxLength(255)
                                                    ->startsWith('/')
                                                    ->rules([
                                                        function (Closure $get) {

                                                            /** @var \Support\RouteUrl\Contracts\HasRouteUrl */
                                                            $term = TaxonomyTerm::with(
                                                                'routeUrls'
                                                            )->find($get('id'));

                                                            return new UniqueActiveRouteUrlRule($term);
                                                        },
                                                        function ($livewire, Closure $get) {

                                                            $datas = $livewire->data['terms'];
                                                            $current_item_id = $get('id');

                                                            return function (string $attribute, $value, Closure $fail) use ($datas,$current_item_id) {
                                                               
                                                                $filtered = array_filter($datas, function ($item) use ($value, $current_item_id) {
                                                                    
                                                                    return isset($item['url']) && $item['url'] === $value && $item['id'] != $current_item_id;
                                                                });
                                                               
                                                                if (! empty($filtered)) {
                                                                    $fail(trans('The :value is already been used.', ['value' => $value]));
                                                                }
                                                            };
                                                        },
                                                    ]),
                                            ]),

                                        ]),

                                    SchemaFormBuilder::make('data', fn (Taxonomy $record) => $record->blueprint->schema),
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
                    ->truncate('max-w-xs 2xl:max-w-xl', true),
                Tables\Columns\TextColumn::make('locale')
                    ->searchable()
                    ->hidden((bool) tenancy()->tenant?->features()->inactive(\App\Features\CMS\Internationalization::class)),
                Tables\Columns\BadgeColumn::make('taxonomy_terms_count')
                    ->counts('taxonomyTerms')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->sortable(),
            ])
            ->filters([])

            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DeleteAction::make()
                        ->using(function (Taxonomy $record) {
                            try {
                                return app(DeleteTaxonomyAction::class)->execute($record);
                            } catch (DeleteRestrictedException) {
                                return false;
                            }
                        }),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->authorize(fn () => Auth::user()?->hasRole(config('domain.role.super_admin'))),
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
            'index' => Resources\TaxonomyResource\Pages\ListTaxonomies::route('/'),
            'create' => Resources\TaxonomyResource\Pages\CreateTaxonomy::route('/create'),
            'edit' => Resources\TaxonomyResource\Pages\EditTaxonomy::route('/{record}/edit'),
        ];
    }

    private static function mapTermWithNormalizedKey(TaxonomyTerm $term): array
    {
        if ($term->relationLoaded('children') && $term->children->isNotEmpty()) {
            $term->setRelation('children', $term->children->mapWithKeys(self::mapTermWithNormalizedKey(...)));
        }

        return ["record-{$term->getKey()}" => $term];
    }
}
