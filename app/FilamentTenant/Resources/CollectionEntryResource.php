<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources;
use App\FilamentTenant\Support\SchemaFormBuilder;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Carbon\Carbon;
use Closure;
use Domain\Collection\Models\CollectionEntry;
use App\FilamentTenant\Support\MetaDataForm;
use Domain\Taxonomy\Models\Taxonomy;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Filament\Facades\Filament;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rules\Unique;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class CollectionEntryResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = CollectionEntry::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $slug = 'entries';

    public static function getRouteBaseName(): string
    {
        return Filament::currentContext() . '.resources.collections.entries';
    }

    public static function getRoutes(): Closure
    {
        return function () {
            $slug = static::getSlug();

            Route::name("collections.{$slug}.")
                ->prefix('collections/{ownerRecord}')
                ->middleware(static::getMiddlewares())
                ->group(function () {
                    foreach (static::getPages() as $name => $page) {
                        Route::get($page['route'], $page['class'])->name($name);
                    }
                });
        };
    }

    /** @param CollectionEntry $record */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [trans('Collection') => $record->collection->name];
    }

    /** @param CollectionEntry $record */
    public static function getGlobalSearchResultUrl(Model $record): ?string
    {
        return self::getUrl('edit', [$record->collection, $record]);
    }

    /** @return Builder<CollectionEntry> */
    protected static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with('collection');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([
                Forms\Components\Group::make([
                    Forms\Components\Group::make()
                        ->schema([
                            Forms\Components\Card::make([
                                Forms\Components\TextInput::make('title')
                                    ->unique(
                                        callback: fn ($livewire, Unique $rule) => $rule->where('collection_id', $livewire->ownerRecord->id),
                                        ignoreRecord: true
                                    )
                                    ->lazy()
                                    ->afterStateUpdated(function (Closure $get, Closure $set, $state) {
                                        if ($get('slug') === Str::slug($state) || blank($get('slug'))) {
                                            $set('slug', Str::slug($state));
                                        }
                                    })
                                    ->required(),
                                Forms\Components\TextInput::make('slug')
                                    ->unique(ignoreRecord: true)
                                    ->dehydrateStateUsing(fn (Closure $get, $state) => Str::slug($state ?: $get('title'))),
                            ]),
                            SchemaFormBuilder::make('data', fn ($livewire) => $livewire->ownerRecord->blueprint->schema),
                        ]),

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
                                                fn (?CollectionEntry $record) => $record?->taxonomyTerms->where('taxonomy_id', $taxonomy->id)
                                                    ->pluck('id')
                                                    ->toArray() ?? []
                                            )
                                    )->toArray()
                                )
                                ->dehydrated(false),
                            Forms\Components\Hidden::make('taxonomy_terms')
                                ->dehydrateStateUsing(fn (Closure $get) => Arr::flatten($get('taxonomies') ?? [], 1)),
                        ])->when(fn ($livewire) => ! empty($livewire->ownerRecord->taxonomies->toArray())),

                    Forms\Components\Section::make(trans('Publishing'))
                        ->schema([
                            Forms\Components\DateTimePicker::make('published_at')
                                ->minDate(Carbon::now()->startOfDay())
                                ->timezone(Auth::user()?->timezone),

                        ])->when(fn ($livewire) => $livewire->ownerRecord->hasPublishDates()),
                ])->columnSpan(2),

                MetaDataForm::make('Meta Data')
                    ->columnSpan(1)
                    ->extraAttributes(['class' => 'md:sticky top-[5.5rem]']),

            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TagsColumn::make('taxonomyTerms.name')
                    ->limit()
                    ->searchable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
            ])
            ->reorderable('order')
            ->actions([
                Tables\Actions\EditAction::make()
                    ->url(fn ($livewire, CollectionEntry $record) => self::getUrl('edit', [$livewire->ownerRecord, $record])),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('order');
    }

    /** @return array */
    public static function getRelations(): array
    {
        return [
            ActivitiesRelationManager::class,
        ];
    }

    /** @return array */
    public static function getPages(): array
    {
        return [
            'index' => Resources\CollectionEntryResource\Pages\ListCollectionEntry::route('entries'),
            'create' => Resources\CollectionEntryResource\Pages\CreateCollectionEntry::route('entries/create'),
            'edit' => Resources\CollectionEntryResource\Pages\EditCollectionEntry::route('entries/{record}/edit'),
        ];
    }
}
