<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources;
use App\FilamentTenant\Resources\CollectionResource\RelationManagers\CollectionEntryRelationManager;
use App\FilamentTenant\Support\SchemaFormBuilder;
use Domain\Collection\Models\Collection;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Carbon\Carbon;
use Closure;
use Domain\Collection\Models\CollectionEntry;
use Domain\Taxonomy\Models\Taxonomy;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Filament\Resources\RelationManagers\RelationGroup;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class CollectionEntryResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = CollectionEntry::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Card::make([
                            Forms\Components\TextInput::make('title')
                                ->unique(ignoreRecord: true)
                                ->required(),
                            Forms\Components\TextInput::make('slug')
                                ->unique(ignoreRecord: true)
                                ->disabled(fn (?CollectionEntry $record) => $record !== null),
                        ]),
                        SchemaFormBuilder::make('data', fn () => $this->ownerRecord->blueprint->schema),
                    ])
                    ->tap(function (Forms\Components\Group $component) {
                        !empty($this->ownerRecord->taxonomies->toArray()) || $this->ownerRecord->hasPublishDates()
                            ? $component->columnSpan(['lg' => 2])
                            : $component->columnSpanFull();
                    }),
                Forms\Components\Card::make([
                    Forms\Components\DateTimePicker::make('published_at')
                        ->minDate(Carbon::now()->startOfDay())
                        ->timezone(Auth::user()?->timezone)
                        ->when(fn (self $livewire) => $livewire->ownerRecord->hasPublishDates()),
                    Forms\Components\Group::make()
                        ->statePath('taxonomies')
                        ->schema(
                            fn () => $this->ownerRecord->taxonomies->map(
                                fn (Taxonomy $taxonomy) => Forms\Components\Select::make($taxonomy->name)
                                    ->statePath((string) $taxonomy->id)
                                    ->multiple()
                                    ->options(
                                        $taxonomy->taxonomyTerms->sortBy('name')
                                            ->mapWithKeys(fn (TaxonomyTerm $term) => [$term->id => $term->name])
                                            ->toArray()
                                    )
                                    ->afterStateHydrated(fn (Forms\Components\Select $component, CollectionEntry $record) => $component->state($record->taxonomyTerms->where('taxonomy_id', $taxonomy->id)->pluck('id')->toArray()))
                            )->toArray()
                        )
                        ->dehydrated(false),
                    Forms\Components\Hidden::make('taxonomy_terms')
                        ->dehydrateStateUsing(fn (Closure $get) => Arr::flatten($get('taxonomies'), 1)),
                ])
                    ->columnSpan(['lg' => 1])
                    ->when(fn () => !empty($this->ownerRecord->taxonomies->toArray()) || $this->ownerRecord->hasPublishDates()),
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
            ->actions([
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
            'entry.create' => Resources\CollectionResource\Pages\CreateCollectionEntry::route('/{ownerRecord}/entry/create'),
            'entry.edit' => Resources\CollectionResource\Pages\EditCollectionEntry::route('/{ownerRecord}/entry/{record}/edit'),
        ];
    }
}
