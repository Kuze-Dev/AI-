<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources;
use App\FilamentTenant\Support\SchemaFormBuilder;
use App\FilamentTenant\Support\Tree;
use Domain\Blueprint\Models\Blueprint;
use Domain\Internationalization\Models\Locale;
use Domain\Taxonomy\Actions\DeleteTaxonomyAction;
use Domain\Taxonomy\Models\Taxonomy;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Support\ConstraintsRelationships\Exceptions\DeleteRestrictedException;

class TaxonomyResource extends Resource
{
    protected static ?string $model = Taxonomy::class;

    protected static ?string $navigationGroup = 'CMS';

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $recordTitleAttribute = 'name';

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
    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->withCount('taxonomyTerms');
    }

    public static function resolveRecordRouteBinding(int|string $key): ?Model
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
                                    Forms\Components\TextInput::make('name')
                                        ->required()
                                        ->unique(ignoreRecord: true),
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
