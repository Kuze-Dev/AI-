<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\PageResource\RelationManagers;

use App\FilamentTenant\Resources\PageResource;
use Domain\Page\Actions\DeletePageAction;
use Domain\Page\Models\Page;
use Filament\Resources\RelationManagers\RelationManager;
// use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;
// use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Support\ConstraintsRelationships\Exceptions\DeleteRestrictedException;

class PageTranslationRelationManager extends RelationManager
{
    protected static string $relationship = 'dataTranslation';

    protected static ?string $recordTitleAttribute = 'pageTranslation';

    public Model $ownerRecord;

    /** @phpstan-ignore missingType.generics, missingType.generics */
    public function getRelationship(): Relation|Builder
    {
        /** @phpstan-ignore property.notFound */
        if ($this->getOwnerRecord()->{static::getRelationshipName()}()->count() > 0 || is_null($this->getOwnerRecord()->translation_id)) {
            return $this->getOwnerRecord()->{static::getRelationshipName()}();
        }

        /** @phpstan-ignore property.notFound, property.notFound, property.notFound */
        return $this->getOwnerRecord()->{static::getRelationshipName()}()->orwhere('id', $this->ownerRecord->translation_id)->orwhere('translation_id', $this->ownerRecord->translation_id)->where('id', '!=', $this->ownerRecord->id);
    }

    #[\Override]
    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        /** @var Builder|Page $query */
                        return $query->Where('name', 'like', "%{$search}%");
                    })
                    ->lineClamp(1)
                    ->wrap(),
                Tables\Columns\TextColumn::make('name')
                    ->hidden()
                    ->searchable()
                    ->lineClamp(1)
                    ->wrap(),
                Tables\Columns\TextColumn::make('locale')
                    ->searchable()
                    ->hidden((bool) \Domain\Tenant\TenantFeatureSupport::inactive(\App\Features\CMS\Internationalization::class)),
                Tables\Columns\TextColumn::make('visibility')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Str::headline($state))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('sites.name')
                    ->hidden((bool) ! (\Domain\Tenant\TenantFeatureSupport::active(\App\Features\CMS\SitesManagement::class)))
                    ->toggleable(condition: fn() => \Domain\Tenant\TenantFeatureSupport::active(\App\Features\CMS\SitesManagement::class), isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('published_at')
                    ->label(trans('Published'))
                    ->icons([
                        'heroicon-o-check-circle' => fn ($state) => $state !== null,
                        'heroicon-o-x-circle' => fn ($state) => $state === null,
                    ])
                    ->color(fn ($state) => $state !== null ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('author.full_name')
                    ->sortable(['first_name', 'last_name'])
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        /** @var Builder|Page $query */
                        return $query->whereHas('author', function ($query) use ($search) {
                            $query->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                    }),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])

            ->actions([
                Tables\Actions\Action::make('edit')
                    ->url(
                        fn (Page $record) => PageResource::getUrl('edit', ['record' => $record])
                    ),
                // ->redirect(function (){
                //     dd(func_get_args());
                // }),
                // ->redirect(fn () => PageResource::getUrl('edit', ['record' => $this->record]) ),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DeleteAction::make()
                        ->using(function (Page $record) {
                            try {
                                return app(DeletePageAction::class)->execute($record);
                            } catch (DeleteRestrictedException) {
                                return false;
                            }
                        }),
                ]),
            ])
            ->bulkActions([

            ])
            ->headerActions([

            ])
            ->defaultSort('id');
    }
}
