<?php

namespace App\FilamentTenant\Resources\ContentEntryResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Illuminate\Database\Eloquent\Relations\Relation;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Domain\Content\Models\ContentEntry;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class ContentEntryTranslationRelationManager extends RelationManager
{
    protected static string $relationship = 'contentEntryTranslation';

    protected static ?string $recordTitleAttribute = 'title';

  /** @phpstan-ignore-next-line */
  public function getRelationship(): Relation|Builder
  {
      if ($this->getOwnerRecord()->{static::getRelationshipName()}()->count() > 0) {
          return $this->getOwnerRecord()->{static::getRelationshipName()}();
      }

      /** @phpstan-ignore-next-line */
      return $this->getOwnerRecord()->{static::getRelationshipName()}()->orwhere('id', $this->ownerRecord->translation_id)->orwhere('translation_id', $this->ownerRecord->translation_id)->where('id', '!=', $this->ownerRecord->id);
  }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                ->sortable()
                ->searchable()
                ->truncate('xs', true),
            Tables\Columns\TextColumn::make('routeUrls.url')
                ->label('URL')
                ->sortable()
                ->searchable()
                ->truncate('xs', true),
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
            Tables\Columns\TagsColumn::make('taxonomyTerms.name')
                ->limit()
                ->searchable(),
            Tables\Columns\TagsColumn::make('sites.name')
                ->hidden((bool) ! (tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class)))
                ->toggleable(condition: function () {
                    return tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class);
                }, isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('published_at')
                ->dateTime(timezone: Auth::user()?->timezone)
                ->sortable()
                ->visible(fn ($livewire) => $livewire->ownerRecord->content->hasPublishDates()),
            Tables\Columns\TextColumn::make('updated_at')
                ->dateTime(timezone: Auth::user()?->timezone)
                ->sortable(),
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
