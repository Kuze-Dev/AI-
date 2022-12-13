<?php

declare (strict_types = 1);

namespace App\FilamentTenant\Resources;

use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources;
use App\FilamentTenant\Support\SchemaFormBuilder;
use Domain\Collection\Models\Collection;
use Domain\Collection\Models\CollectionEntry;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Filament\Forms\FormsComponent;
use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;

class CollectionEntryResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = CollectionEntry::class;

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    protected static ?string $navigationGroup = 'CMS';

    public static function form(Form $form): Form
    {
        return $form->schema([
            SchemaFormBuilder::make(
                'data',
                fn (RelationManager $livewire) => dd($livewire->ownerRecord) 
            )
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

            ])
            ->filters([

            ])
            ->actions([

            ])
            ->bulkActions([

            ])
            ->defaultSort('updated_at', 'desc');
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
            'index' => Resources\CollectionEntryResource\Pages\ListCollectionEntry::route('/')
        ];
    }
}