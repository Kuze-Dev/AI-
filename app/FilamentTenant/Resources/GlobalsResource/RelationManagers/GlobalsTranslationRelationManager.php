<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\GlobalsResource\RelationManagers;

use App\FilamentTenant\Resources\GlobalsResource;
use Domain\Globals\Models\Globals;
use Filament\Resources\RelationManagers\RelationManager;
// use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;

class GlobalsTranslationRelationManager extends RelationManager
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
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('locale')
                    ->searchable()
                    ->hidden((bool) tenancy()->tenant?->features()->inactive(\App\Features\CMS\Internationalization::class)),
                Tables\Columns\TextColumn::make('sites.name')
                    ->badge()
                    ->hidden((bool) ! (tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class)))
                    ->toggleable(condition: function () {
                        return tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class);
                    }, isToggledHiddenByDefault: true),
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
                        fn (Globals $record) => GlobalsResource::getUrl('edit', ['record' => $record])
                    ),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
