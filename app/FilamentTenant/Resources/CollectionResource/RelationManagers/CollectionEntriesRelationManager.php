<?php

declare (strict_types = 1);

namespace App\FilamentTenant\Resources\CollectionResource\RelationManagers;

use App\FilamentTenant\Support\SchemaFormBuilder;
use Closure;
use Domain\Collection\Models\CollectionEntry;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Actions\AssociateAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
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

    /**
     * @param Table $table
     * 
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ViewColumn::make('data')
                    ->view('filament.table.columns.data-transformer')
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make('Create collection entry'),
                Tables\Actions\AssociateAction::make()
                    ->disabled()
                    ->hidden()
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\DissociateAction::make()
                    ->disabled()
                    ->hidden(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }    

    /**
     * @return string|null
     */
    protected function getTableReorderColumn(): ?string
    {
        return 'order';
    }
    
    /**
     * @return bool
     */
    protected function canReorder(): bool
    {
        return $this->ownerRecord->is_sortable == 0 ? false : true; 
    }

    /**
     * @return Builder
     */
    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
