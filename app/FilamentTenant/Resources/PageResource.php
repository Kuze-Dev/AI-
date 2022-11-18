<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Domain\Page\Enums\PageBehavior;
use Domain\Page\Models\Page;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Exception;

class PageResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = Page::class;

    protected static ?string $navigationGroup = 'CMS';

    protected static ?string $navigationIcon = 'heroicon-o-view-boards';

    protected static ?string $recordTitleAttribute = 'name';

    /** @throws Exception */
    public static function table(Table $table): Table
    {
        $behaviorsFilterOptions = collect(PageBehavior::cases())
            ->mapWithKeys(fn (PageBehavior $fieldType) => [
                $fieldType->value => $fieldType->label(),
            ]);

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('blueprint.name')
                    ->sortable()
                    ->searchable()
                    ->url(fn (Page $record) => BlueprintResource::getUrl('edit', $record->blueprint)),
                Tables\Columns\BadgeColumn::make('past_behavior')
                    ->formatStateUsing(fn (Page $record) => $record->past_behavior?->label() ?? '--')
                    ->colors(fn (Page $record) => [$record->past_behavior?->color() ?? ''])
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\BadgeColumn::make('future_behavior')
                    ->formatStateUsing(fn (Page $record) => $record->future_behavior?->label() ?? '--')
                    ->colors(fn (Page $record) => [$record->future_behavior?->color() ?? ''])
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('published_at')
                    ->label(trans('Published date'))
                    ->date(timezone: Auth::user()?->timezone)
                    ->sortable()
                    ->toggleable(),
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
                Tables\Filters\SelectFilter::make('blueprint')
                    ->relationship('blueprint', 'name')
                    ->searchable()
                    ->optionsLimit(20),
                Tables\Filters\TernaryFilter::make('published_at')
                    ->label(trans('Published date'))
                    ->nullable(),
                Tables\Filters\SelectFilter::make('has_behavior')
                    ->options(['1' => 'Yes', '0' => 'No'])
                    ->query(function (Builder $query, array $data) {
                        $query->when(filled($data['value']), function (Builder $query) use ($data) {
                            $query->when(filled($data['value']), function (Builder $query) use ($data) {
                                /** @var Page|Builder $query */
                                match ($data['value']) {
                                    '1' => $query->whereNotNull('past_behavior')->whereNotNull('future_behavior'),
                                    '0' => $query->whereNull('past_behavior')->whereNull('future_behavior'),
                                    default => '',
                                };
                            });
                        });
                    }),
                Tables\Filters\SelectFilter::make('past_behavior')
                    ->multiple()
                    ->options($behaviorsFilterOptions)
                    ->query(function (Builder $query, array $data) {
                        $query->when(filled($data['values']), function (Builder $query) use ($data) {
                            /** @var Page|Builder $query */
                            $query->whereIn('past_behavior', $data['values']);
                        });
                    }),
                Tables\Filters\SelectFilter::make('future_behavior')
                    ->multiple()
                    ->options($behaviorsFilterOptions)
                    ->query(function (Builder $query, array $data) {
                        $query->when(filled($data['values']), function (Builder $query) use ($data) {
                            /** @var Page|Builder $query */
                            $query->whereIn('future_behavior', $data['values']);
                        });
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Resources\PageResource\Pages\ListPages::route('/'),
            'create' => Resources\PageResource\Pages\CreatePage::route('/create'),
            'edit' => Resources\PageResource\Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
