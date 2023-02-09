<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources;
use App\FilamentTenant\Support\Tree;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Domain\Taxonomy\Models\Taxonomy;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms;
use Illuminate\Support\Str;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use App\FilamentTenant\Support\SchemaFormBuilder;
use Domain\Blueprint\Models\Blueprint;

class TaxonomyResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = Taxonomy::class;

    protected static ?string $navigationGroup = 'CMS';

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'taxonomyTerms.name'];
    }

    /** @param Taxonomy $record */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
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
                        ->reactive()
                        ->afterStateUpdated(function (Closure $set, $state) {
                            $set('slug', Str::slug($state));
                        })->required()
                        ->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('slug')->required()
                        ->disabled(fn (?Taxonomy $record) => $record !== null)
                        ->unique(ignoreRecord: true)
                        ->rules('alpha_dash')
                        ->disabled(),
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
                        ->disabled(fn (?Taxonomy $record) => $record !== null),
                ]),

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
                                    Forms\Components\TextInput::make('name')
                                        ->reactive()
                                        ->afterStateUpdated(function (Closure $set, $state) {
                                            $set('slug', Str::slug($state));
                                        })->required()
                                        ->unique(ignoreRecord: true),
                                    Forms\Components\TextInput::make('slug')->required()
                                        ->disabled(fn (?TaxonomyTerm $record) => $record !== null)
                                        ->unique(ignoreRecord: true)
                                        ->rules('alpha_dash')
                                        ->disabled(),
                                    SchemaFormBuilder::make('data', fn (Taxonomy $record) => $record->blueprint?->schema),
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
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('slug'),
                Tables\Columns\BadgeColumn::make('taxonomy_terms_count')
                    ->counts('taxonomyTerms')
                    ->sortable(),
            ])
            ->filters([])
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
