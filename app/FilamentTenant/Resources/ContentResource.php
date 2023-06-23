<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use Domain\Content\Enums\PublishBehavior;
use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources;
use Domain\Blueprint\Models\Blueprint;
use Domain\Content\Models\Content;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Closure;
use Domain\Content\Actions\DeleteContentAction;
use Domain\Support\ConstraintsRelationships\Exceptions\DeleteRestrictedException;
use Domain\Taxonomy\Models\Taxonomy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class ContentResource extends Resource
{
    use ContextualResource;

    /** @var string|null */
    protected static ?string $model = Content::class;

    /** @var string|null */
    protected static ?string $navigationIcon = 'heroicon-o-collection';

    /** @var string|null */
    protected static ?string $navigationGroup = 'CMS';

    /** @var string|null */
    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'contentEntries.title'];
    }

    /** @return Builder<Content> */
    protected static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->withCount('contentEntries');
    }

    /** @param Content $record */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        /** @phpstan-ignore-next-line */
        return [trans('Total Entries') => $record->content_entries_count];
    }

    /**
     * @param Form $form
     *
     * @return Form
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make([
                    Forms\Components\TextInput::make('name')
                        ->unique(ignoreRecord: true)
                        ->lazy()
                        ->afterStateUpdated(function (Closure $get, Closure $set, $state) {
                            if ($get('prefix') === Str::slug($state) || blank($get('prefix'))) {
                                $set('prefix', Str::slug($state));
                            }
                        })
                        ->required(),
                    Forms\Components\Select::make('blueprint_id')
                        ->label(trans('Blueprint'))
                        ->required()
                        ->preload()
                        ->optionsFromModel(Blueprint::class, 'name')
                        ->disabled(fn (?Content $record) => $record !== null),
                    Forms\Components\TextInput::make('prefix')
                        ->required()
                        ->string()
                        ->alphaDash()
                        ->unique(ignoreRecord: true)
                        ->dehydrateStateUsing(fn (Closure $get, $state) => Str::slug($state ?: $get('name'))),
                    Forms\Components\Select::make('taxonomies')
                        ->multiple()
                        ->preload()
                        ->optionsFromModel(Taxonomy::class, 'name')
                        ->afterStateHydrated(function (Forms\Components\Select $component, ?Content $record) {
                            $component->state($record ? $record->taxonomies->pluck('id')->toArray() : []);
                        }),
                    Forms\Components\Card::make([
                        Forms\Components\Toggle::make('display_publish_dates')
                            ->helperText(trans('Enable publish date visibility and behavior of contents'))
                            ->reactive()
                            ->afterStateHydrated(fn (?Content $record, Forms\Components\Toggle $component) => $component->state($record && $record->hasPublishDates()))
                            ->dehydrated(false),
                        Forms\Components\Grid::make(['sm' => 2])
                            ->schema([
                                Forms\Components\Select::make('past_publish_date_behavior')
                                    ->options(
                                        collect(PublishBehavior::cases())
                                            ->mapWithKeys(fn (PublishBehavior $behaviorType) => [
                                                $behaviorType->value => Str::headline($behaviorType->value),
                                            ])
                                            ->toArray()
                                    )
                                    ->searchable()
                                    ->columnSpan(['sm' => 1])
                                    ->required(),
                                Forms\Components\Select::make('future_publish_date_behavior')
                                    ->options(
                                        collect(PublishBehavior::cases())
                                            ->mapWithKeys(fn (PublishBehavior $behaviorType) => [
                                                $behaviorType->value => Str::headline($behaviorType->value),
                                            ])
                                            ->toArray()
                                    )
                                    ->searchable()
                                    ->columnSpan(['sm' => 1])
                                    ->required(),
                            ])->when(fn (Closure $get) => $get('display_publish_dates')),
                    ]),

                    Forms\Components\Card::make([
                        Forms\Components\Toggle::make('is_sortable')
                            ->label(trans('Allow ordering'))
                            ->helperText(trans('Grants option for ordering of content entries'))
                            ->reactive(),
                    ]),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('blueprint')
                    ->relationship('blueprint', 'name')
                    ->searchable()
                    ->optionsLimit(20),
            ])

            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('view-entries')
                        ->icon('heroicon-s-eye')
                        ->color('secondary')
                        ->url(fn (Content $record) => ContentEntryResource::getUrl('index', [$record])),
                    Tables\Actions\DeleteAction::make()
                        ->using(function (Content $record) {
                            try {
                                return app(DeleteContentAction::class)->execute($record);
                            } catch (DeleteRestrictedException $e) {
                                return false;
                            }
                        }),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    /** @return array */
    public static function getRelations(): array
    {
        return [
            ActivitiesRelationManager::class,
        ];
    }

    /** @return array */
    public static function getPages(): array
    {
        return [
            'index' => Resources\ContentResource\Pages\ListContent::route('/'),
            'create' => Resources\ContentResource\Pages\CreateContent::route('/create'),
            'edit' => Resources\ContentResource\Pages\EditContent::route('/{record}/edit'),
        ];
    }
}
