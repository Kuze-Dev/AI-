<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\PageResource\RelationManagers;

use Domain\Page\Actions\DeletePageAction;
use Domain\Page\Models\Page;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Support\ConstraintsRelationships\Exceptions\DeleteRestrictedException;

class PageTranslationRelationManager extends RelationManager
{
    protected static string $relationship = 'pageTranslation';

    protected static ?string $recordTitleAttribute = 'pageTranslation';

    public Model $ownerRecord;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        /** @var Builder|Page $query */
                        return $query->Where('name', 'like', "%{$search}%");
                    })
                    ->truncate('xs', true),
                Tables\Columns\TextColumn::make('name')
                    ->hidden()
                    ->searchable()
                    ->truncate('xs', true),
                Tables\Columns\TextColumn::make('locale')
                    ->searchable()
                    ->hidden((bool) tenancy()->tenant?->features()->inactive(\App\Features\CMS\Internationalization::class)),
                Tables\Columns\BadgeColumn::make('visibility')
                    ->formatStateUsing(fn ($state) => Str::headline($state))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TagsColumn::make('sites.name')
                    ->hidden((bool) ! (tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class)))
                    ->toggleable(condition: function () {
                        return tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class);
                    }, isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('published_at')
                    ->label(trans('Published'))
                    ->options([
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
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->sortable(),
            ])

            ->actions([
                Tables\Actions\EditAction::make(),
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
