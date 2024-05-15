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
use Domain\Taxonomy\Actions\DeleteTaxonomyAction;
use Domain\Taxonomy\Models\Taxonomy;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Support\ConstraintsRelationships\Exceptions\DeleteRestrictedException;
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
                        ->maxLength(255)
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
                                                ->unique(ignoreRecord: true),
                                            Forms\Components\Group::make([
                                                // Forms\Components\Toggle::make('is_override')
                                                // ->formatStateUsing(function(Closure $get) {

                                                //     $term = TaxonomyTerm::with(
                                                //         'routeUrls'
                                                //     )->findorFail($get('id'));
                                                //     dd($term);
                                                // })
                                                // ->label(trans('Custom URL'))
                                                // ->reactive(),

                                                Forms\Components\TextInput::make('url')
                                                    ->label(trans('URL'))
                                                    ->reactive()
                                                    ->disabled(fn ($livewire) => ! $livewire->data['has_route'])
                                                    ->hidden(fn ($livewire) => ! $livewire->data['has_route'])
                                                    // ->formatStateUsing(fn (?HasRouteUrl $record) => $record?->activeRouteUrl?->url)
                                                    ->formatStateUsing(function (Closure $get, $livewire) {

                                                        $term = TaxonomyTerm::with(
                                                            'routeUrls'
                                                        )->find($get('id'));

                                                        return $term ? $term->ActiveRouteurl?->url : null;

                                                    })
                                                    ->required()
                                                    ->string()
                                                    ->maxLength(255)
                                                    ->startsWith('/')
                                                    ->rule(
                                                        function (Closure $get) {

                                                            /** @var \Support\RouteUrl\Contracts\HasRouteUrl */
                                                            $term = TaxonomyTerm::with(
                                                                'routeUrls'
                                                            )->find($get('id'));

                                                            return tenancy()->tenant?->features()->inactive(SitesManagement::class) ?
                                                            new UniqueActiveRouteUrlRule($term) : null;
                                                        }
                                                        // new MicroSiteUniqueRouteUrlRule($record, $get('sites'))
                                                    ),
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
