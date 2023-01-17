<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources;
use App\FilamentTenant\Resources\TaxonomyResource\RelationManagers\TaxonomyTermsRelationManager;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Domain\Taxonomy\Models\Taxonomy;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Forms;
use Illuminate\Support\Str;
use Closure;

use Filament\Resources\RelationManagers\RelationGroup;

class TaxonomyResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = Taxonomy::class;

    protected static ?string $navigationGroup = 'CMS';

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
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
                ]),
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
            RelationGroup::make('Main', [
                TaxonomyTermsRelationManager::class,
                ActivitiesRelationManager::class,
            ]),
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
}
