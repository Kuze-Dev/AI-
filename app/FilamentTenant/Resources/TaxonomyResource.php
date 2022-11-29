<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\FilamentTenant\Resources;
use App\FilamentTenant\Resources\TaxonomyResource\RelationManagers\TaxonomyTermsRelationManager;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Domain\Taxonomy\Models\Taxonomy;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

use Illuminate\Support\Str;
use Closure;

use Filament\Forms\Components\Card;
use Filament\Forms\Components\TextInput;

use Filament\Tables\Columns\TextColumn;

class TaxonomyResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = Taxonomy::class;

    protected static ?string $navigationGroup = 'CMS';

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()->schema([
                    TextInput::make('name')
                        ->reactive()
                        ->afterStateUpdated(function (Closure $set, $state) {
                            $set('slug', Str::slug($state));
                        })->required()
                        ->unique(ignoreRecord: true),
                    TextInput::make('slug')->required()
                        ->disabled(fn (?Taxonomy $record) => $record !== null)
                        ->unique(ignoreRecord: true),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->sortable()->searchable(),
                TextColumn::make('slug'),
            ])
            ->filters([

            ])
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
            TaxonomyTermsRelationManager::class,
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
