<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources;
use App\FilamentTenant\Support\MetaDataForm;
use App\FilamentTenant\Support\RouteUrlFieldset;
use App\FilamentTenant\Support\SchemaFormBuilder;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Closure;
use Domain\Internationalization\Models\Locale;
use Domain\Page\Actions\DeletePageAction;
use Domain\Page\Enums\Visibility;
use Domain\Page\Models\Block;
use Domain\Page\Models\BlockContent;
use Domain\Page\Models\Page;
use Domain\Site\Models\Site;
use Exception;
use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use Support\ConstraintsRelationships\Exceptions\DeleteRestrictedException;
use Support\RouteUrl\Rules\MicroSiteUniqueRouteUrlRule;

class PageResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = Page::class;

    protected static ?string $navigationGroup = 'CMS';

    protected static ?string $navigationIcon = 'heroicon-o-document';

    protected static ?string $recordTitleAttribute = 'name';

    /** @var Collection<int, Block> */
    public static ?Collection $cachedBlocks = null;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Card::make([
                            Forms\Components\TextInput::make('name')
                                ->unique(
                                    ignoreRecord: true,
                                    callback: function (Unique $rule, $state, $livewire) {

                                        if (tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class) || tenancy()->tenant?->features()->active(\App\Features\CMS\Internationalization::class)) {
                                            return false;
                                        }

                                        if ($livewire->record?->parentPage?->name == $state) {
                                            return false;
                                        }

                                        return $rule;
                                    }
                                )
                                ->lazy()
                                ->afterStateUpdated(function (Forms\Components\TextInput $component) {
                                    $component->getContainer()
                                        ->getComponent(fn (Component $component) => $component->getId() === 'route_url')
                                        ?->dispatchEvent('route_url::update');
                                })
                                ->required()
                                ->string()
                                ->maxLength(255),
                            RouteUrlFieldset::make(),
                            // ->disabled(fn (?Page $record) => $record?->isHomePage()),
                            Forms\Components\Select::make('locale')
                                ->options(Locale::all()->sortByDesc('is_default')->pluck('name', 'code')->toArray())
                                ->default((string) Locale::where('is_default', true)->first()?->code)
                                ->searchable()
                                ->hidden((bool) tenancy()->tenant?->features()->inactive(\App\Features\CMS\Internationalization::class))
                                ->reactive()
                                ->afterStateUpdated(function (Forms\Components\Select $component, Closure $get) {
                                    $component->getContainer()
                                        ->getComponent(fn (Component $component) => $component->getId() === 'route_url')
                                        ?->dispatchEvent('route_url::update');
                                })
                                ->required(),
                            Forms\Components\Group::make([
                                Forms\Components\Toggle::make('published_at')
                                    ->label(trans('Published'))
                                    ->formatStateUsing(fn (Carbon|bool|null $state) => $state instanceof Carbon ? true : (bool) $state)
                                    ->dehydrateStateUsing(fn (?bool $state) => $state ? now() : null)
                                    ->disabled(fn (?Page $record) => $record?->isHomePage()),
                                Forms\Components\Select::make('visibility')
                                    ->options(
                                        collect(Visibility::cases())
                                            ->mapWithKeys(fn (Visibility $visibility) => [
                                                $visibility->value => Str::headline($visibility->value),
                                            ])
                                            ->toArray()
                                    )
                                    ->disabled(fn (?Page $record) => $record?->isHomePage())
                                    ->default(Visibility::PUBLIC->value)
                                    ->required(),
                            ])
                                ->columns('grid-cols-[10rem,1fr] items-center'),
                            Forms\Components\Hidden::make('author_id')
                                ->default(Auth::id()),
                        ]),
                        Forms\Components\Card::make([
                            Forms\Components\CheckboxList::make('sites')
                                ->reactive()
                                ->required(fn () => tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class))
                                ->rule(fn (?Page $record, Closure $get) => new MicroSiteUniqueRouteUrlRule($record, $get('route_url')))
                                ->options(function () {

                                    if (Auth::user()?->hasRole(config('domain.role.super_admin'))) {
                                        return Site::orderBy('name')
                                            ->pluck('name', 'id')
                                            ->toArray();
                                    }

                                    return Site::orderBy('name')
                                        ->whereHas('siteManager', fn ($query) => $query->where('admin_id', Auth::user()?->id))
                                        ->pluck('name', 'id')
                                        ->toArray();
                                })
                                ->afterStateHydrated(function (Forms\Components\CheckboxList $component, ?Page $record): void {
                                    if (! $record) {
                                        $component->state([]);

                                        return;
                                    }

                                    $component->state(
                                        $record->sites->pluck('id')
                                            ->intersect(array_keys($component->getOptions()))
                                            ->values()
                                            ->toArray()
                                    );
                                }),
                        ])
                            ->hidden((bool) tenancy()->tenant?->features()->inactive(\App\Features\CMS\SitesManagement::class)),
                        Forms\Components\Repeater::make('block_contents')
                            ->afterStateHydrated(function (Forms\Components\Repeater $component, ?Page $record, ?array $state) {
                                if ($record === null || $record->blockContents->isEmpty()) {
                                    $component->state($state ?? []);

                                    return;
                                }

                                $component->state(
                                    $record->blockContents->sortBy('order')
                                        ->mapWithKeys(fn (BlockContent $item) => ["record-{$item->getKey()}" => $item])
                                        ->toArray()
                                );

                                // WORKAROUND: Force after state hydrate after setting the new state
                                foreach ($component->getChildComponentContainers() as $componentContainer) {
                                    $componentContainer->callAfterStateHydrated();
                                }
                            })
                            ->itemLabel(fn (array $state) => self::getCachedBlocks()->firstWhere('id', $state['block_id'])?->name)
                            ->label('Blocks')
                            ->default([])
                            ->collapsed(fn (string $context) => $context === 'edit')
                            ->orderable('order')
                            ->schema([
                                Forms\Components\ViewField::make('block_id')
                                    ->label('Block')
                                    ->required()
                                    ->view('filament.forms.components.block-picker')
                                    ->viewData([
                                        'blocks' => self::getCachedBlocks()
                                            ->sortBy('name')
                                            ->mapWithKeys(function (Block $block) {
                                                return [
                                                    $block->id => [
                                                        'name' => $block['name'],
                                                        'image' => $block->getFirstMediaUrl('image'),
                                                    ],
                                                ];
                                            })
                                            ->toArray(),
                                    ])
                                    ->reactive()
                                    ->afterStateUpdated(function ($component, $state) {
                                        $block = self::getCachedBlocks()->firstWhere('id', $state);
                                        $component->getContainer()
                                            ->getComponent(fn ($component) => $component->getId() === 'schema-form')
                                            ?->getChildComponentContainer()
                                            ->fill($block?->is_fixed_content ? $block->data : []);
                                    }),
                                SchemaFormBuilder::make('data')
                                    ->id('schema-form')
                                    ->dehydrated(fn (Closure $get) => ! (self::getCachedBlocks()->firstWhere('id', $get('block_id'))?->is_fixed_content))
                                    ->disabled(fn (Closure $get) => self::getCachedBlocks()->firstWhere('id', $get('block_id'))?->is_fixed_content ?? false)
                                    ->schemaData(fn (Closure $get) => self::getCachedBlocks()->firstWhere('id', $get('block_id'))?->blueprint->schema),
                            ]),
                    ])->columnSpan(2),
                MetaDataForm::make('Meta Data')
                    ->columnSpan(1),
            ]);
    }

    /** @throws Exception */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->truncate('xs', true),
                Tables\Columns\TextColumn::make('name')
                    ->hidden()
                    ->searchable()
                    ->truncate('xs', true),
                Tables\Columns\TextColumn::make('activeRouteUrl.url')
                    ->label('URL')
                    ->sortable()
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
            ->filters([
                Tables\Filters\SelectFilter::make('visibility')
                    ->options(
                        collect(Visibility::cases())
                            ->mapWithKeys(fn (Visibility $visibility) => [
                                $visibility->value => Str::headline($visibility->value),
                            ])
                            ->toArray()
                    ),
                Tables\Filters\SelectFilter::make('locale')
                    ->options(Locale::all()->sortByDesc('is_default')->pluck('name', 'code')->toArray()),
                Tables\Filters\SelectFilter::make('sites')
                    ->multiple()
                    ->hidden((bool) ! (tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class)))
                    ->relationship('sites', 'name'),
                Tables\Filters\TernaryFilter::make('published_at')
                    ->label(trans('Published'))
                    ->nullable(),
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
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    /** @return Builder<\Domain\Page\Models\Page> */
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

    public static function getRecordTitle(?Model $record): ?string
    {

        $status = '';

        if ($record) {
            /** @var Page */
            $model = $record;
            $status = $model->draftable_id ? ' ( Draft )' : '';
        }

        /** @var string */
        $attribute = static::$recordTitleAttribute;
        $recordTitle = $record?->getAttribute($attribute) ?? '';

        $maxLength = 60; // Maximum length for the title before truncating
        $truncatedTitle = Str::limit($recordTitle, $maxLength, '...');

        return $truncatedTitle.''.$status;
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

    /** @return Collection<int, Block> $cachedBlocks */
    protected static function getCachedBlocks(): Collection
    {
        if (! isset(self::$cachedBlocks)) {
            self::$cachedBlocks = Block::with(['blueprint', 'media'])->get();
        }

        return self::$cachedBlocks;
    }
}
