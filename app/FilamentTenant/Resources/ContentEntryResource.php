<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Features\CMS\Internationalization;
use App\Features\CMS\SitesManagement;
use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources;
use App\FilamentTenant\Support\MetaDataForm;
use App\FilamentTenant\Support\RouteUrlFieldset;
use App\FilamentTenant\Support\SchemaFormBuilder;
use Closure;
use Domain\Content\Models\Builders\ContentEntryBuilder;
use Domain\Content\Models\ContentEntry;
use Domain\Internationalization\Models\Locale;
use Domain\Taxonomy\Models\Taxonomy;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Domain\Tenant\TenantFeatureSupport;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use Support\RouteUrl\Rules\MicroSiteUniqueRouteUrlRule;

class ContentEntryResource extends Resource
{
    protected static ?string $model = ContentEntry::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $slug = 'entries';

    public static function getRouteBaseName(?string $panel = null): string
    {
        return Filament::currentContext().'.resources.contents.entries';
    }

    public static function getRoutes(): Closure
    {
        return function () {
            $slug = static::getSlug();

            Route::name("contents.{$slug}.")
                ->prefix('contents/{ownerRecord}')
                ->middleware(static::getMiddlewares())
                ->group(function () {
                    foreach (static::getPages() as $name => $page) {
                        Route::get($page['route'], $page['class'])->name($name);
                    }
                });
        };
    }

    /** @param  ContentEntry  $record */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        /** @phpstan-ignore-next-line */
        return [trans('Content') => $record->content->name];
    }

    /** @param  ContentEntry  $record */
    public static function getGlobalSearchResultUrl(Model $record): ?string
    {
        return self::getUrl('edit', [$record->content, $record]);
    }

    /** @return Builder<ContentEntry> */
    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with('content');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([
                Forms\Components\Group::make([
                    Forms\Components\Card::make([
                        Forms\Components\TextInput::make('title')
                            ->unique(
                                callback: function ($livewire, Unique $rule) {

                                    if (TenantFeatureSupport::someAreActive([SitesManagement::class,
                                        Internationalization::class])) {

                                        return false;
                                    }

                                    return $rule->where('content_id', $livewire->ownerRecord->id);
                                },
                                ignoreRecord: true
                            )
                            ->lazy()
                            ->afterStateUpdated(function (Forms\Components\TextInput $component) {
                                $component->getContainer()
                                    ->getComponent(fn (Component $component) => $component->getId() === 'route_url')
                                    ?->dispatchEvent('route_url::update');
                            })
                            ->required()
                            ->string()
                            ->maxLength(255),
                        RouteUrlFieldset::make()
                            ->generateModelForRouteUrlUsing(function ($livewire, ContentEntry|string $model) {
                                return $model instanceof ContentEntry
                                    ? $model
                                    : tap(new ContentEntry())->setRelation('content', $livewire->ownerRecord);
                            }),
                        Forms\Components\Select::make('locale')
                            ->options(Locale::all()->sortByDesc('is_default')->pluck('name', 'code')->toArray())
                            ->default((string) Locale::where('is_default', true)->first()?->code)
                            ->searchable()
                            ->hidden((bool) TenantFeatureSupport::inactive(Internationalization::class))
                            ->reactive()
                            ->afterStateUpdated(function (Forms\Components\Select $component, \Filament\Forms\Get $get) {
                                $component->getContainer()
                                    ->getComponent(fn (Component $component) => $component->getId() === 'route_url')
                                    ?->dispatchEvent('route_url::update');
                            })
                            ->required(),
                        Forms\Components\Hidden::make('author_id')
                            ->default(Auth::id()),
                    ]),
                    Forms\Components\Card::make([
                        Forms\Components\CheckboxList::make('sites')
                            ->required(fn () => TenantFeatureSupport::active(SitesManagement::class))
                            ->rule(fn (?ContentEntry $record, \Filament\Forms\Get $get) => new MicroSiteUniqueRouteUrlRule($record, $get('route_url')))
                            ->options(function ($livewire) {

                                /** @var \Domain\Admin\Models\Admin */
                                $user = Auth::user();

                                if ($user->hasRole(config('domain.role.super_admin'))) {
                                    return $livewire->ownerRecord->sites->pluck('name', 'id')
                                        ->toArray();
                                }

                                return $livewire->ownerRecord->sites
                                    ->whereIN('id', $user->userSite->pluck('id')->toArray())
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->afterStateHydrated(function (Forms\Components\CheckboxList $component, ?ContentEntry $record): void {
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
                        ->hidden((bool) ! (TenantFeatureSupport::active(SitesManagement::class))),
                    Forms\Components\Section::make(trans('Taxonomies'))
                        ->schema([
                            Forms\Components\Group::make()
                                ->statePath('taxonomies')
                                ->schema(
                                    fn ($livewire) => $livewire->ownerRecord->taxonomies->map(
                                        fn (Taxonomy $taxonomy) => Forms\Components\Select::make($taxonomy->name)
                                            ->statePath((string) $taxonomy->id)
                                            ->multiple()
                                            ->options(
                                                $taxonomy->taxonomyTerms->sortBy('name')
                                                    ->mapWithKeys(fn (TaxonomyTerm $term) => [$term->id => $term->name])
                                                    ->toArray()
                                            )
                                            ->formatStateUsing(
                                                fn (?ContentEntry $record) => $record?->taxonomyTerms->where('taxonomy_id', $taxonomy->id)
                                                    ->pluck('id')
                                                    ->toArray() ?? []
                                            )
                                    )->toArray()
                                )
                                ->dehydrated(false),
                            Forms\Components\Hidden::make('taxonomy_terms')
                                ->dehydrateStateUsing(fn (\Filament\Forms\Get $get) => Arr::flatten($get('taxonomies') ?? [], 1)),
                        ])
                        ->when(fn ($livewire) => ! empty($livewire->ownerRecord->taxonomies->toArray())),
                    Forms\Components\Section::make(trans('Publishing'))
                        ->schema([
                            Forms\Components\DateTimePicker::make('published_at')
                                ->timezone(Auth::user()?->timezone),
                        ])
                        ->when(fn ($livewire) => $livewire->ownerRecord->hasPublishDates()),
                    SchemaFormBuilder::make('data', fn ($livewire) => $livewire->ownerRecord->blueprint->schema),
                ])->columnSpan(2),

                MetaDataForm::make('Meta Data')
                    ->columnSpan(1),

            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->sortable()
                    ->searchable()
                    ->truncate('xs', true),
                Tables\Columns\TextColumn::make('routeUrls.url')
                    ->label('URL')
                    ->sortable()
                    ->searchable()
                    ->truncate('xs', true),
                Tables\Columns\TextColumn::make('locale')
                    ->searchable()
                    ->hidden(TenantFeatureSupport::inactive(Internationalization::class)),
                Tables\Columns\TextColumn::make('author.full_name')
                    ->sortable(['first_name', 'last_name'])
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        /** @var Builder|ContentEntry $query */
                        return $query->whereHas('author', function ($query) use ($search) {
                            $query->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                    }),
                Tables\Columns\TagsColumn::make('taxonomyTerms.name')
                    ->limit()
                    ->searchable(),
                Tables\Columns\TagsColumn::make('sites.name')
                    ->hidden((bool) ! (TenantFeatureSupport::active(SitesManagement::class)))
                    ->toggleable(condition: function () {
                        return TenantFeatureSupport::active(SitesManagement::class);
                    }, isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->sortable()
                    ->visible(fn ($livewire) => $livewire->ownerRecord->hasPublishDates()),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('taxonomies')
                    ->form(fn ($livewire) => $livewire->ownerRecord->taxonomies->map(
                        fn (Taxonomy $taxonomy) => Forms\Components\Select::make($taxonomy->name)
                            ->statePath($taxonomy->slug)
                            ->multiple()
                            ->options(
                                $taxonomy->taxonomyTerms->sortBy('name')
                                    ->mapWithKeys(fn (TaxonomyTerm $term) => [$term->slug => $term->name])
                                    ->toArray()
                            )
                    )->toArray())
                    ->query(function (ContentEntryBuilder $query, array $data): Builder {
                        foreach ($data as $taxonomySlug => $taxonomyTermSlugs) {
                            if (filled($taxonomyTermSlugs)) {
                                $query->whereTaxonomyTerms($taxonomySlug, $taxonomyTermSlugs);
                            }
                        }

                        return $query;
                    })
                    ->visible(fn ($livewire) => $livewire->ownerRecord->taxonomies->isNotEmpty()),
                Tables\Filters\Filter::make('published_at_year_month')
                    ->form([
                        Forms\Components\Select::make('published_at_year')
                            ->placeholder('Select Year')
                            ->searchable()
                            ->options(
                                collect(range(1900, now()->addYears(10)->year))
                                    ->mapWithKeys(fn (int $year) => [$year => $year])
                                    ->toArray()
                            )
                            ->debounce(),
                        Forms\Components\Select::make('published_at_month')
                            ->options(
                                collect(range(1, 12))
                                    ->mapWithKeys(fn (int $month) => [$month => now()->month($month)->format('F')])
                                    ->toArray()
                            )
                            ->disabled(fn (\Filament\Forms\Get $get) => blank($get('published_at_year')))
                            ->helperText(fn (\Filament\Forms\Get $get) => blank($get('published_at_year')) ? 'Enter a published at year first.' : null),
                    ])
                    ->query(fn (ContentEntryBuilder $query, array $data): Builder => $query->when(
                        filled($data['published_at_year']),
                        fn (ContentEntryBuilder $query) => $query->wherePublishedAtYearMonth(
                            (int) $data['published_at_year'],
                            filled($data['published_at_month']) ? (int) $data['published_at_month'] : null
                        )
                    ))
                    ->visible(fn ($livewire) => $livewire->ownerRecord->hasPublishDates()),
                Tables\Filters\Filter::make('published_at_range')
                    ->form([
                        Forms\Components\DatePicker::make('published_at_from'),
                        Forms\Components\DatePicker::make('published_at_to'),
                    ])
                    ->query(fn (ContentEntryBuilder $query, array $data): Builder => $query->wherePublishedAtRange(
                        filled($data['published_at_from']) ? Carbon::parse($data['published_at_from']) : null,
                        filled($data['published_at_to']) ? Carbon::parse($data['published_at_to']) : null,
                    ))
                    ->visible(fn ($livewire) => $livewire->ownerRecord->hasPublishDates()),
            ])
            ->reorderable('order')

            ->actions([
                Tables\Actions\EditAction::make()
                    ->url(fn ($livewire, ContentEntry $record) => self::getUrl('edit', [$livewire->ownerRecord, $record])),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DeleteAction::make(),
                ]),

            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('order');
    }

    public static function getRecordTitle(?Model $record): ?string
    {

        $status = '';

        if ($record) {
            /** @var ContentEntry */
            $model = $record;
            $status = $model->draftable_id ? ' ( Draft )' : '';
        }

        /** @var string */
        $attribute = static::$recordTitleAttribute;
        $recordTitle = $record?->getAttribute($attribute) ?? '';

        $maxLength = 60; // Maximum length for the title before truncating
        $truncatedTitle = Str::limit($recordTitle, $maxLength, '...');

        return $truncatedTitle.''.$status;
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
            'index' => Resources\ContentEntryResource\Pages\ListContentEntry::route('entries'),
            'create' => Resources\ContentEntryResource\Pages\CreateContentEntry::route('entries/create'),
            'edit' => Resources\ContentEntryResource\Pages\EditContentEntry::route('entries/{record}/edit'),
        ];
    }
}
