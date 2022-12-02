<?php

declare (strict_types = 1);

namespace App\FilamentTenant\Resources\CollectionResource\RelationManagers;

use App\FilamentTenant\Support\SchemaFormBuilder;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Livewire\Component as LivewireComponent;
use Livewire\Livewire;

class CollectionEntriesRelationManager extends RelationManager
{
    protected static ?string $modelLabel = 'Collection Entries';
    
    protected static string $relationship = 'collectionEntries';

    protected static ?string $recordTitleAttribute = null;

    /**
     * Get the form schema from reference 
     * blueprint and inject to SchemaFormBuilder 
     * to produce the final form fields.
     * 
     * @param Form $form
     * 
     * @return Form
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                SchemaFormBuilder::make(
                    'data', 
                    fn (RelationManager $livewire) => $livewire->ownerRecord->blueprint->schema
                )
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('null'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }    
}
