<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources;
use Closure;
use Domain\Blueprint\Models\Blueprint;
use Domain\Content\Actions\DeleteContentAction;
use Domain\Content\Enums\PublishBehavior;
use Domain\Content\Models\Content;
use Domain\Site\Models\Site;
use Domain\Taxonomy\Models\Taxonomy;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Support\ConstraintsRelationships\Exceptions\DeleteRestrictedException;

class ContentResource extends Resource
{
    protected static ?string $model = Content::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'CMS';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'contentEntries.title'];
    }

    /** @return Builder<Content> */
    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->withCount('contentEntries');
    }

    /** @param  Content  $record */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        /** @phpstan-ignore-next-line */
        return [trans('Total Entries') => $record->content_entries_count];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make([
                    Forms\Components\TextInput::make('name')
                        ->unique(ignoreRecord: true)
                        ->string()
                        ->maxLength(255)
                        ->lazy()
                        ->afterStateUpdated(function (\Filament\Forms\Get $get, \Filament\Forms\Set $set, $state) {
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
                        ->maxLength(255)
                        ->alphaDash()
                        ->unique(ignoreRecord: true)
                        ->dehydrateStateUsing(fn (\Filament\Forms\Get $get, $state) => Str::slug($state ?: $get('name'))),
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
                            ])->when(fn (\Filament\Forms\Get $get) => $get('display_publish_dates')),
                    ]),

                    Forms\Components\Card::make([
                        Forms\Components\Toggle::make('is_sortable')
                            ->label(trans('Allow ordering'))
                            ->helperText(trans('Grants option for ordering of content entries'))
                            ->reactive(),
                    ]),

                    Forms\Components\Card::make([
                        Forms\Components\CheckboxList::make('sites')
                            ->required(fn () => tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class))
                            ->rules([
                                function (?Content $record, \Filament\Forms\Get $get) {

                                    return function (string $attribute, $value, Closure $fail) use ($record, $get) {

                                        $siteIDs = $value;

                                        if ($record) {
                                            $siteIDs = array_diff($siteIDs, $record->sites->pluck('id')->toArray());

                                            $content = Content::where('name', $get('name'))
                                                ->where('id', '!=', $record->id)
                                                ->whereHas(
                                                    'sites',
                                                    fn ($query) => $query->whereIn('site_id', $siteIDs)
                                                )->count();

                                        } else {

                                            $content = Content::where('name', $get('name'))->whereHas(
                                                'sites',
                                                fn ($query) => $query->whereIn('site_id', $siteIDs)
                                            )->count();
                                        }

                                        if ($content > 0) {
                                            $fail("Content {$get('name')} is already available in selected sites.");
                                        }

                                    };
                                },
                            ])
                            ->options(
                                fn () => Site::orderBy('name')
                                    ->pluck('name', 'id')
                                    ->toArray()
                            )
                            ->formatStateUsing(fn (?Content $record) => $record ? $record->sites->pluck('id')->toArray() : []),

                    ])
                        ->hidden((bool) ! (tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class) && Auth::user()?->hasRole(config('domain.role.super_admin')))),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable()
                    ->truncate('max-w-xs xl:max-w-md 2xl:max-w-2xl', true),
                Tables\Columns\TagsColumn::make('sites.name')
                    ->hidden((bool) ! (tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class)))
                    ->toggleable(condition: function () {
                        return tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class);
                    }, isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('sites')
                    ->multiple()
                    ->hidden((bool) ! (tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class)))
                    ->relationship('sites', 'name'),
                Tables\Filters\SelectFilter::make('blueprint')
                    ->relationship('blueprint', 'name')
                    ->hidden((bool) ! Auth::user()?->can('blueprint.viewAny'))
                    ->searchable()
                    ->optionsLimit(20),
            ])

            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('view-entries')
                        ->icon('heroicon-s-eye')
                        ->color('gray')
                        ->url(fn (Content $record) => ContentEntryResource::getUrl('index', [$record])),
                    Tables\Actions\DeleteAction::make()
                        ->using(function (Content $record) {
                            try {
                                return app(DeleteContentAction::class)->execute($record);
                            } catch (DeleteRestrictedException) {
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

    public static function getRelations(): array
    {
        return [
            ActivitiesRelationManager::class,
        ];
    }

    /** @return Builder<\Domain\Content\Models\Content> */
    public static function getEloquentQuery(): Builder
    {
        if (Auth::user()?->hasRole(config('domain.role.super_admin'))) {
            return static::getModel()::query();
        }

        if (tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class) &&
            Auth::user()?->can('site.siteManager') &&
            ! (Auth::user()->hasRole(config('domain.role.super_admin')))
        ) {
            return static::getModel()::query()->wherehas('sites', function ($q) {
                return $q->whereIn('site_id', Auth::user()?->userSite->pluck('id')->toArray());
            });
        }

        return static::getModel()::query();

    }

    public static function getPages(): array
    {
        return [
            'index' => Resources\ContentResource\Pages\ListContent::route('/'),
            'create' => Resources\ContentResource\Pages\CreateContent::route('/create'),
            'edit' => Resources\ContentResource\Pages\EditContent::route('/{record}/edit'),
        ];
    }
}
