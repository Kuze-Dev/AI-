<?php

declare (strict_types = 1);

namespace App\FilamentTenant\Resources\CollectionResource\RelationManagers;

use App\FilamentTenant\Support\SchemaFormBuilder;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;

class CollectionEntryRelationManager extends RelationManager
{
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
                Tables\Columns\TextColumn::make('title')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->sortable()
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
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make('Create collection entry')
                    ->url(fn (self $livewire) => route('filament-tenant.resources.collections.entry.create', $livewire->getOwnerRecord())),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
        return $this->ownerRecord->is_sortable;
    }
}
