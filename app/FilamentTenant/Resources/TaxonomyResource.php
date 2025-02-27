<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Features\CMS\Internationalization;
use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources;
use App\FilamentTenant\Resources\TaxonomyResource\RelationManagers\TaxonomyTranslationRelationManager;
use App\FilamentTenant\Support\RouteUrlFieldset;
use App\FilamentTenant\Support\SchemaFormBuilder;
use App\FilamentTenant\Support\Tree;
use Closure;
use Domain\Blueprint\Models\Blueprint;
use Domain\Internationalization\Models\Locale;
use Domain\Site\Models\Site;
use Domain\Taxonomy\Actions\DeleteTaxonomyAction;
use Domain\Taxonomy\Models\Taxonomy;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Domain\Tenant\TenantFeatureSupport;
use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
// use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use Support\ConstraintsRelationships\Exceptions\DeleteRestrictedException;
use Support\RouteUrl\Rules\MicroSiteUniqueRouteUrlRule;
use Support\RouteUrl\Rules\UniqueActiveRouteUrlRule;

class TaxonomyResource extends Resource
{
    protected static ?string $model = Taxonomy::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $recordTitleAttribute = 'name';

    #[\Override]
    public static function getNavigationGroup(): ?string
    {
        return trans('CMS');
    }

    #[\Override]
    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'taxonomyTerms.name'];
    }

    /** @param  Taxonomy  $record */
    #[\Override]
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return array_filter([
            'Total terms' => $record->taxonomy_terms_count,
            'Selected Sites' => implode(',', $record->sites()->pluck('name')->toArray()),
        ]);
    }

    /** @return Builder<Taxonomy> */
    #[\Override]
    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->withCount('taxonomyTerms');
    }

    // #[\Override]
    // public static function resolveRecordRouteBinding(int|string $key): ?Model

    /** @return Builder<\Domain\Taxonomy\Models\Taxonomy> */
    public static function getEloquentQuery(): Builder
    {
        if (filament_admin()->hasRole(config('domain.role.super_admin'))) {
            return static::getModel()::query();
        }

        if (tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class) &&
            filament_admin()->can('site.siteManager') &&
            ! (filament_admin()->hasRole(config('domain.role.super_admin')))
        ) {
            return static::getModel()::query()->wherehas('sites', function ($q) {
                return $q->whereIn('site_id', filament_admin()->userSite->pluck('id')->toArray());
            });
        }

        return static::getModel()::query();

    }

    #[\Override]
    public static function resolveRecordRouteBinding(mixed $key): ?Model
    {
        return app(static::getModel())
            ->resolveRouteBindingQuery(static::getEloquentQuery(), $key, static::getRecordRouteKeyName())
            ->with('parentTerms.children')
            ->first();
    }

    #[\Override]
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()->schema([
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
                        ->unique(
                            ignoreRecord: true,
                            modifyRuleUsing: function (Unique $rule, $state, $livewire) {

                                if (tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class) || tenancy()->tenant?->features()->active(\App\Features\CMS\Internationalization::class)) {
                                    return false;
                                }

                                return $rule;
                            }
                        )
                        ->lazy(),
                    Forms\Components\Select::make('blueprint_id')
                        ->label(trans('Blueprint'))
                        ->required()
                        ->preload()
                        ->optionsFromModel(Blueprint::class, 'name')
                        ->disableOptionWhen(fn (?Taxonomy $record) => $record !== null),
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
                        ->disabled(fn (\Filament\Forms\Get $get) => ! $get('has_route'))
                        ->hidden(fn (\Filament\Forms\Get $get) => ! $get('has_route')),
                ]),
                Forms\Components\Select::make('locale')
                    ->options(Locale::all()->sortByDesc('is_default')->pluck('name', 'code')->toArray())
                    ->default((string) Locale::where('is_default', true)->first()?->code)
                    ->searchable()
                    ->rules([
                        function (?Taxonomy $record, \Filament\Forms\Get $get) {

                            return function (string $attribute, $value, Closure $fail) use ($record, $get) {

                                if ($record) {
                                    $selectedLocale = $value;

                                    $originalContentId = $record->translation_id ?: $record->id;

                                    $exist = Taxonomy::where(fn ($query) => $query->where('translation_id', $originalContentId)->orWhere('id', $originalContentId)
                                    )->where('locale', $selectedLocale)->first();

                                    if ($exist && $exist->id != $record->id) {
                                        $fail("Taxonomy {$get('name')} has a existing ({$selectedLocale}) translation.");
                                    }
                                }

                            };
                        },
                    ])
                    ->hidden((bool) tenancy()->tenant?->features()->inactive(\App\Features\CMS\Internationalization::class))
                    ->required(),

                Forms\Components\Section::make([
                    // Forms\Components\CheckboxList::make('sites')
                    \App\FilamentTenant\Support\CheckBoxList::make('sites')
                        ->reactive()
                        ->required(fn () => tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class))
                        ->rule(fn (?Taxonomy $record, \Filament\Forms\Get $get) => new MicroSiteUniqueRouteUrlRule($record, $get('route_url')))
                        ->options(function () {
                            return Site::orderBy('name')
                                ->pluck('name', 'id')
                                ->toArray();
                        })
                        ->disableOptionWhen(function (string $value, Forms\Components\CheckboxList $component) {

                            /** @var \Domain\Admin\Models\Admin */
                            $user = filament_admin();

                            if ($user->hasRole(config('domain.role.super_admin'))) {
                                return false;
                            }

                            $user_sites = $user->userSite->pluck('id')->toArray();

                            $intersect = array_intersect(array_keys($component->getOptions()), $user_sites);

                            return ! in_array($value, $intersect);
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
                                                ->afterStateUpdated(function (\Filament\Forms\Set $set, $state, $livewire) {
                                                    $set('url', $livewire->data['route_url']['url'].'/'.Str::of($state)->slug());

                                                    return $state;
                                                })
                                                ->unique(ignoreRecord: true),
                                            Forms\Components\Group::make([
                                                Forms\Components\Toggle::make('is_custom')
                                                    ->formatStateUsing(function (\Filament\Forms\Get $get, $state) {

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
                                                    ->disabled(fn ($livewire, \Filament\Forms\Get $get) => ! ($livewire->data['has_route'] && $get('is_custom')))
                                                    ->hidden(fn ($livewire) => ! $livewire->data['has_route'])
                                                    ->formatStateUsing(function (\Filament\Forms\Get $get, $state, $livewire) {

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
                                                        function (\Filament\Forms\Get $get) {

                                                            /** @var \Support\RouteUrl\Contracts\HasRouteUrl */
                                                            $term = TaxonomyTerm::with(
                                                                'routeUrls'
                                                            )->find($get('id'));

                                                            return new UniqueActiveRouteUrlRule($term);
                                                        },
                                                        function ($livewire, \Filament\Forms\Get $get) {

                                                            $datas = $livewire->data['terms'];
                                                            $current_item_id = $get('id');

                                                            return function (string $attribute, $value, Closure $fail) use ($datas, $current_item_id) {

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
                Tables\Columns\TextColumn::make('taxonomy_terms_count')
                    ->badge()
                    ->counts('taxonomyTerms')
                    ->sortable(),
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
                    ->authorize(fn () => filament_admin()->hasRole(config('domain.role.super_admin'))),
            ]);
    }

    #[\Override]
    public static function getRelations(): array
    {
        return [
            ActivitiesRelationManager::class,
            TaxonomyTranslationRelationManager::class,
        ];
    }

    #[\Override]
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
