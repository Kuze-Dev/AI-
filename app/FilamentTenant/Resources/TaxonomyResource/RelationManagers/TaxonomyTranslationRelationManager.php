<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\TaxonomyResource\RelationManagers;

use App\FilamentTenant\Resources\TaxonomyResource;
use Domain\Taxonomy\Models\Taxonomy;
use Filament\Resources\RelationManagers\RelationManager;
// use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;

class TaxonomyTranslationRelationManager extends RelationManager
{
    protected static string $relationship = 'dataTranslation';

    protected static ?string $recordTitleAttribute = 'name';

    /** @phpstan-ignore-next-line */
    public function getRelationship(): Relation|Builder
    {
        /** @phpstan-ignore-next-line */
        if ($this->getOwnerRecord()->{static::getRelationshipName()}()->count() > 0 || is_null($this->getOwnerRecord()->translation_id)) {
            return $this->getOwnerRecord()->{static::getRelationshipName()}();
        }

        /** @phpstan-ignore-next-line */
        return $this->getOwnerRecord()->{static::getRelationshipName()}()->orwhere('id', $this->ownerRecord->translation_id)->orwhere('translation_id', $this->ownerRecord->translation_id)->where('id', '!=', $this->ownerRecord->id);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable()
                    ->lineClamp(1)
                    ->wrap(),
                Tables\Columns\TextColumn::make('locale')
                    ->searchable()
                    ->hidden((bool) tenancy()->tenant?->features()->inactive(\App\Features\CMS\Internationalization::class)),
                Tables\Columns\TextColumn::make('taxonomy_terms_count')
                    ->badge()
                    ->counts('taxonomyTerms')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([

            ])
            ->actions([
                Tables\Actions\Action::make('edit')
                    ->url(
                        fn (Taxonomy $record) => TaxonomyResource::getUrl('edit', ['record' => $record])
                    ),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
