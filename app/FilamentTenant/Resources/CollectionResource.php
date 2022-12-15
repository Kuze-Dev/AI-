<?php

declare (strict_types = 1);

namespace App\FilamentTenant\Resources;

use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources;
use App\FilamentTenant\Resources\CollectionResource\RelationManagers\CollectionEntriesRelationManager;
use Domain\Blueprint\Models\Blueprint;
use Domain\Collection\Models\Collection;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Closure;
use Filament\Forms\FormsComponent;
use Filament\Resources\RelationManagers\RelationGroup;
use Illuminate\Support\Facades\Auth;

class CollectionResource extends Resource
{
    use ContextualResource;

    /**
     * @var string|null
     */
    protected static ?string $model = Collection::class;

    /**
     * @var string|null
     */
    protected static ?string $navigationIcon = 'heroicon-o-collection';

    /**
     * @var string|null
     */
    protected static ?string $navigationGroup = 'CMS';

    /**
     * @var string|null
     */
    protected static ?string $recordTitleAttribute = 'name';

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
                        ->required(),
                    Forms\Components\TextInput::make('slug')
                        ->unique(ignoreRecord: true)
                        ->disabled(fn (?Collection $record) => $record !== null),
                    Forms\Components\Select::make('blueprint_id')
                        ->relationship('blueprint', 'name')
                        ->saveRelationshipsUsing(null)
                        ->required()
                        ->exists(Blueprint::class, 'id')
                        ->searchable()
                        ->preload()
                        ->reactive()
                        ->helperText(function (?Collection $record, ?string $state) {
                            if ($record === null) {
                                return;
                            }

                            if ($record->blueprint_id !== (int) $state) {
                                return trans('Modifying the blueprint will reset all the page\'s content.');
                            }
                        }),

                    Forms\Components\Card::make([
                        Forms\Components\Toggle::make('display_publish_dates')
                            ->helperText(trans('Enable publish date visibility and behavior of collections'))
                            ->reactive()
                            ->afterStateHydrated(fn (?Collection $record, Forms\Components\Toggle $component) => $component->state($record && $record->hasPublishDates()))
                            ->dehydrated(false),
                        Forms\Components\Grid::make(12)
                            ->schema([
                                Forms\Components\Select::make('past_publish_date')
                                    ->options([
                                        'public' => 'Public',
                                        'private' => 'Private',
                                        'unlisted' => 'Unlisted'
                                    ])
                                    ->default('public')
                                    ->searchable()
                                    ->columnSpan(6)
                                    ->required(),
                                Forms\Components\Select::make('future_publish_date')
                                    ->options([
                                        'public' => 'Public',
                                        'private' => 'Private',
                                        'unlisted' => 'Unlisted'
                                    ])
                                    ->default('public')
                                    ->searchable()
                                    ->columnSpan(6)
                                    ->required()
                        ])->when(fn (Closure $get) => $get('display_publish_dates')),

                    ]),

                    Forms\Components\Card::make([
                        Forms\Components\Toggle::make('is_sortable')
                            ->label(trans('Allow ordering'))
                            ->helperText(trans('Grants option for ordering of collection entries'))
                            ->reactive(),
                    ])
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
            Tables\Columns\TextColumn::make('slug')
                ->sortable()
                ->searchable(),
            Tables\Columns\TextColumn::make('blueprint.name')
                ->sortable()
                ->searchable()
                ->url(fn (Collection $record) => BlueprintResource::getUrl('edit', $record->blueprint)),
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
        ])
        ->actions([
            Tables\Actions\Action::make('configure')
                ->authorize('collection.configure')
                ->icon('heroicon-s-cog')
                ->url(fn (Collection $record) => route('filament-tenant.resources.'. self::getSlug() . '.configure', $record)),
            Tables\Actions\DeleteAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
        ])
        ->defaultSort('updated_at', 'desc');
    }

    /**
     * @return array
     */
    public static function getRelations(): array
    {
        return [
            RelationGroup::make('Main', [
                CollectionEntriesRelationManager::class,
                ActivitiesRelationManager::class,
            ]),
        ];
    }

    /**
     * @return array
     */
    public static function getPages(): array
    {
        return [
            'index' => Resources\CollectionResource\Pages\ListCollection::route('/'),
            'create' => Resources\CollectionResource\Pages\CreateCollection::route('/create'),
            'configure' => Resources\CollectionResource\Pages\ConfigureCollection::route('/{record}/configure'),
            'entry.create' => Resources\CollectionResource\Pages\CreateCollectionEntry::route('/{ownerRecord}/create'),
            'entry.edit' => Resources\CollectionResource\Pages\EditCollectionEntry::route('/{ownerRecord}/entry/{record}/edit'),
        ];
    }
}
