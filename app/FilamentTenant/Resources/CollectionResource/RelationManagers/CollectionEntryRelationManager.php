<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\CollectionResource\RelationManagers;

use Domain\Collection\Models\CollectionEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\Facades\Auth;

class CollectionEntryRelationManager extends RelationManager
{
    protected static string $relationship = 'collectionEntries';

    protected static ?string $recordTitleAttribute = null;

    /**
     * Set components for the
     * relation manager table.
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
                Tables\Columns\TextColumn::make('taxonomyTerm.name')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([

            ])
            ->headerActions([
                Tables\Actions\CreateAction::make('Create collection entry')
                    ->url(fn (self $livewire) => route('filament-tenant.resources.collections.entry.create', $livewire->getOwnerRecord())),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->url(fn (self $livewire, CollectionEntry $record) => route('filament-tenant.resources.collections.entry.edit', [
                        'ownerRecord' => $livewire->ownerRecord,
                        'record' => $record,
                    ])),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('order', 'desc');;
    }

    /**
     * Set default column
     * storage for ordering.
     *
     * @return string|null
     */
    protected function getTableReorderColumn(): ?string
    {
        return 'order';
    }

    /**
     * Determine if collection entries
     * can be re-ordered.
     */
    protected function canReorder(): bool
    {
        return $this->ownerRecord->is_sortable;
    }

    protected function getDefaultTableSortDirection(): ?string
    {
        return 'asc';
    }
}
