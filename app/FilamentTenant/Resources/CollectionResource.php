<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\FilamentTenant\Support\RouteUrlForm;
use Domain\Collection\Enums\PublishBehavior;
use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources;
use Domain\Blueprint\Models\Blueprint;
use Domain\Collection\Models\Collection;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Filters\Layout;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Closure;
use Domain\Taxonomy\Models\Taxonomy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class CollectionResource extends Resource
{
    use ContextualResource;

    /** @var string|null */
    protected static ?string $model = Collection::class;

    /** @var string|null */
    protected static ?string $navigationIcon = 'heroicon-o-collection';

    /** @var string|null */
    protected static ?string $navigationGroup = 'CMS';

    /** @var string|null */
    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'collectionEntries.title'];
    }

    /** @return Builder<Collection> */
    protected static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->withCount('collectionEntries');
    }

    /** @param Collection $record */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [trans('Total Entries') => $record->collection_entries_count];
    }

    /**
     * @param Form $form
     *
     * @return Form
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make([
                    Forms\Components\TextInput::make('name')
                        ->unique(ignoreRecord: true)
                        ->lazy()
                        ->afterStateUpdated(function (Closure $get, Closure $set, $state) {
                            if ($get('slug') === Str::slug($state) || blank($get('slug'))) {
                                $set('slug', Str::slug($state));
                            }
                        })
                        ->required(),
                    Forms\Components\Select::make('blueprint_id')
                        ->required()
                        ->options(
                            fn () => Blueprint::orderBy('name')
                                ->pluck('name', 'id')
                                ->toArray()
                        )
                        ->exists(Blueprint::class, 'id')
                        ->searchable()
                        ->preload()
                        ->disabled(fn (?Collection $record) => $record !== null),
                    Forms\Components\Select::make('taxonomies')
                        ->multiple()
                        ->options(
                            fn () => Taxonomy::orderBy('name')
                                ->pluck('name', 'id')
                                ->toArray()
                        )
                        ->searchable()
                        ->afterStateHydrated(function (Forms\Components\Select $component, ?Collection $record) {
                            $component->state($record ? $record->taxonomies->pluck('id')->toArray() : []);
                        }),
                    Forms\Components\Card::make([
                        Forms\Components\Toggle::make('display_publish_dates')
                            ->helperText(trans('Enable publish date visibility and behavior of collections'))
                            ->reactive()
                            ->afterStateHydrated(fn (?Collection $record, Forms\Components\Toggle $component) => $component->state($record && $record->hasPublishDates()))
                            ->dehydrated(false),
                        Forms\Components\Grid::make(['sm' => 2])
                            ->schema([
                                Forms\Components\Select::make('past_publish_date_behavior')
                                    ->options(
                                        collect(PublishBehavior::cases())
                                            ->mapWithKeys(fn (PublishBehavior $behaviorType) => [
                                                $behaviorType->value => Str::headline($behaviorType->value),
                                            ])
                                            ->toArray()
                                    )
                                    ->searchable()
                                    ->columnSpan(['sm' => 1])
                                    ->required(),
                                Forms\Components\Select::make('future_publish_date_behavior')
                                    ->options(
                                        collect(PublishBehavior::cases())
                                            ->mapWithKeys(fn (PublishBehavior $behaviorType) => [
                                                $behaviorType->value => Str::headline($behaviorType->value),
                                            ])
                                            ->toArray()
                                    )
                                    ->searchable()
                                    ->columnSpan(['sm' => 1])
                                    ->required(),
                            ])->when(fn (Closure $get) => $get('display_publish_dates')),
                    ]),

                    Forms\Components\Card::make([
                        Forms\Components\Toggle::make('is_sortable')
                            ->label(trans('Allow ordering'))
                            ->helperText(trans('Grants option for ordering of collection entries'))
                            ->reactive(),
                    ]),
                ]),
                RouteUrlForm::make('Route Url')
                    ->applySchema(Collection::class),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('blueprint.name')
                    ->sortable()
                    ->searchable()
                    ->url(fn (Collection $record) => BlueprintResource::getUrl('edit', $record->blueprint)),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('blueprint')
                    ->relationship('blueprint', 'name')
                    ->searchable()
                    ->optionsLimit(20),
            ])
            ->filtersLayout(Layout::AboveContent)
            ->actions([
                Tables\Actions\Action::make('view-entries')
                    ->icon('heroicon-s-eye')
                    ->color('secondary')
                    ->url(fn (Collection $record) => CollectionEntryResource::getUrl('index', [$record])),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('updated_at', 'desc');
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
            'index' => Resources\CollectionResource\Pages\ListCollection::route('/'),
            'create' => Resources\CollectionResource\Pages\CreateCollection::route('/create'),
            'edit' => Resources\CollectionResource\Pages\EditCollection::route('/{record}/edit'),
        ];
    }
}
