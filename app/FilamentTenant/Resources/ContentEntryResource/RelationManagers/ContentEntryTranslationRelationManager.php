<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ContentEntryResource\RelationManagers;

use App\FilamentTenant\Resources\ContentEntryResource;
use Domain\Content\Models\ContentEntry;
use Filament\Resources\RelationManagers\RelationManager;
// use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;

class ContentEntryTranslationRelationManager extends RelationManager
{
    protected static string $relationship = 'dataTranslation';

    protected static ?string $recordTitleAttribute = 'title';

    /** @phpstan-ignore missingType.generics, missingType.generics */
    public function getRelationship(): Relation|Builder
    {
        /** @phpstan-ignore property.notFound */
        if ($this->getOwnerRecord()->{static::getRelationshipName()}()->count() > 0 || is_null($this->getOwnerRecord()->translation_id)) {
            return $this->getOwnerRecord()->{static::getRelationshipName()}();
        }

        /** @phpstan-ignore property.notFound, property.notFound, property.notFound */
        return $this->getOwnerRecord()->{static::getRelationshipName()}()->orwhere('id', $this->ownerRecord->translation_id)->orwhere('translation_id', $this->ownerRecord->translation_id)->where('id', '!=', $this->ownerRecord->id)->with('content');
    }

    #[\Override]
    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->sortable()
                    ->searchable()
                    ->lineClamp(1)
                    ->wrap(),
                Tables\Columns\TextColumn::make('routeUrls.url')
                    ->label('URL')
                    ->sortable()
                    ->searchable()
                    ->lineClamp(1)
                    ->wrap(),
                Tables\Columns\TextColumn::make('locale')
                    ->searchable()
                    ->hidden((bool) tenancy()->tenant?->features()->inactive(\App\Features\CMS\Internationalization::class)),
                Tables\Columns\TextColumn::make('author.full_name')
                    ->sortable(['first_name', 'last_name'])
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        /** @var Builder|ContentEntry $query */
                        return $query->whereHas('author', function ($query) use ($search) {
                            $query->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                    }),
                Tables\Columns\TextColumn::make('taxonomyTerms.name')
                    ->badge()
                    ->limit()
                    ->searchable(),
                Tables\Columns\TextColumn::make('sites.name')
                    ->badge()
                    ->hidden((bool) ! (tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class)))
                    ->toggleable(condition: fn() => tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class), isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable()
                    ->visible(fn ($livewire) => $livewire->ownerRecord->content->hasPublishDates()),
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
                        fn (ContentEntry $record) => ContentEntryResource::getUrl('edit', [$record->content, $record])
                    ),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
