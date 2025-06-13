<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Features\CMS\Internationalization;
use App\Features\CMS\SitesManagement;
use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources;
use App\FilamentTenant\Resources\PageResource\RelationManagers\PageTranslationRelationManager;
use App\FilamentTenant\Support\MetaDataForm;
use App\FilamentTenant\Support\RouteUrlFieldset;
use App\FilamentTenant\Support\SchemaFormBuilder;
use Closure;
use Domain\Internationalization\Models\Locale;
use Domain\Page\Actions\DeletePageAction;
use Domain\Page\Enums\Visibility;
use Domain\Page\Models\Block;
use Domain\Page\Models\BlockContent;
use Domain\Page\Models\Page;
use Domain\Site\Models\Site;
use Domain\Tenant\TenantFeatureSupport;
use Exception;
use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use Support\ConstraintsRelationships\Exceptions\DeleteRestrictedException;
use Support\RouteUrl\Rules\MicroSiteUniqueRouteUrlRule;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-document';

    protected static ?string $recordTitleAttribute = 'name';

    #[\Override]
    public static function getNavigationGroup(): ?string
    {
        return trans('CMS');
    }

    /** @param  Page  $record */
    #[\Override]
    public static function getGlobalSearchResultDetails(Model $record): array
    {

        return array_filter([
            'Page' => $record->name,
            'Selected Sites' => implode(',', $record->sites()->pluck('name')->toArray()),
        ]);
    }

    /** @var Collection<int, Block> */
    public static ?Collection $cachedBlocks = null;

    #[\Override]
    public static function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make([
                            Forms\Components\TextInput::make('name')
                                ->unique(
                                    ignoreRecord: true,
                                    modifyRuleUsing: function (Unique $rule, $state, $livewire) {

                                        if (TenantFeatureSupport::active(SitesManagement::class) || TenantFeatureSupport::active(Internationalization::class)) {
                                            return false;
                                        }

                                        if ($livewire->record?->parentPage?->name === $state) {
                                            return false;
                                        }

                                        return $rule;
                                    }
                                )
                                ->lazy()
                                ->afterStateUpdated(function (Forms\Components\TextInput $component, \Filament\Forms\Get $get) {
                                    if (! $get('route_url.is_override')) {
                                        $component->getContainer()
                                            ->getComponent(fn (Component $component) => $component->getId() === 'route_url')
                                            ?->dispatchEvent('route_url::update');
                                    }
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
                                ->rules([
                                    fn (?Page $record, \Filament\Forms\Get $get) => function (string $attribute, $value, Closure $fail) use ($record, $get) {

                                        if ($record) {
                                            $selectedLocale = $value;

                                            $originalContentId = $record->translation_id ?: $record->id;

                                            $exist = Page::where(fn ($query) => $query->where('translation_id', $originalContentId)->orWhere('id', $originalContentId)
                                            )->where('locale', $selectedLocale)->first();

                                            if ($exist && $exist->id !== $record->id) {
                                                $fail("Page {$get('name')} has a existing ({$selectedLocale}) translation.");
                                            }
                                        }

                                    },
                                ])
                                ->hidden((bool) \Domain\Tenant\TenantFeatureSupport::inactive(\App\Features\CMS\Internationalization::class))
                                ->reactive()
                                ->afterStateUpdated(function (Forms\Components\Select $component, \Filament\Forms\Get $get) {
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
                                ->default(filament_Admin()->getKey()),
                        ]),
                        Forms\Components\Section::make([
                            // Forms\Components\CheckboxList::make('sites')
                            \App\FilamentTenant\Support\CheckBoxList::make('sites')
                                ->reactive()
                                ->required(fn () => \Domain\Tenant\TenantFeatureSupport::active(\App\Features\CMS\SitesManagement::class))
                                ->rules([
                                    fn (?Page $record, \Filament\Forms\Get $get) => new MicroSiteUniqueRouteUrlRule($record, $get('route_url')),
                                    fn (?Page $record, \Filament\Forms\Get $get) => function (string $attribute, $value, Closure $fail) use ($get) {

                                        if (\Domain\Tenant\TenantFeatureSupport::active(\App\Features\CMS\SitesManagement::class)) {

                                            $block_ids = array_values(
                                                array_filter(array_map(fn ($item) => $item['block_id'] ?? null, $get('block_contents'))
                                                ));

                                            $siteIDs = $value;

                                            $block_siteIds = self::getCachedBlocks()
                                                ->filter(fn ($block) => $block->sites->pluck('id')->intersect($siteIDs)->isNotEmpty())->pluck('id')->toArray();

                                            foreach ($block_ids as $block_id) {

                                                if (! in_array($block_id, $block_siteIds, true)) {
                                                    $fail('A block added to the page is not available with the selected sites. Please review the sites field or ensure that only blocks available for the selected sites are added.');
                                                }
                                            }

                                        }

                                    },
                                ])
                                ->options(fn () => Site::orderBy('name')
                                    ->pluck('name', 'id')
                                    ->toArray())
                                ->disableOptionWhen(function (string $value, Forms\Components\CheckboxList $component) {

                                    /** @var \Domain\Admin\Models\Admin */
                                    $admin = filament_admin();

                                    if ($admin->hasRole(config()->string('domain.role.super_admin'))) {
                                        return false;
                                    }

                                    $user_sites = $admin->userSite->pluck('id')->toArray();

                                    $intersect = array_intersect(array_keys($component->getOptions()), $user_sites);

                                    return in_array($value, $intersect, true);

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
                            ->hidden((bool) TenantFeatureSupport::inactive(SitesManagement::class)),
                        Forms\Components\Repeater::make('block_contents')
                            ->afterStateHydrated(function (Forms\Components\Repeater $component, ?Page $record, ?array $state) {
                                if ($record === null || $record->blockContents->isEmpty()) {
                                    $component->state($state ?? []);

                                    return;
                                }

                                $component->state(
                                    $record->blockContents->sortBy('order')
                                        ->mapWithKeys(fn (BlockContent $item) => [
                                            "record-{$item->getKey()}" => array_merge(
                                                $item->toArray(),
                                                [
                                                    'data' => (array) $item->data,
                                                    'block' => [],
                                                ]
                                            ),
                                        ])
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
                            ->orderColumn('order')
                            ->schema([
                                // Forms\Components\ViewField::make('block_id')
                                \App\Filament\Livewire\Forms\CustomViewField::make('block_id')
                                    ->label('Block')
                                    ->required()
                                    ->view('filament.forms.components.block-picker')
                                    ->datafilter(fn (\Filament\Forms\Get $get) => self::getCachedBlocks()
                                        ->filter(fn ($block) => $block->sites->pluck('id')->intersect($get('../../sites'))->isNotEmpty())
                                        ->pluck('id')->toArray()
                                    )
                                    ->viewData(fn () => [
                                        'blocks' => self::getCachedBlocks()
                                            ->sortBy('name')
                                            ->mapWithKeys(fn (Block $block) => [
                                                $block->id => [
                                                    'name' => $block['name'],
                                                    'image' => $block->getFirstMediaUrl('image'),
                                                ],
                                            ])
                                            ->toArray()])
                                    ->reactive()
                                    ->afterStateUpdated(function (Forms\Components\ViewField $component, $state) {
                                        $block = self::getCachedBlocks()->firstWhere('id', $state);
                                        $component->getContainer()
                                            ->getComponent(fn ($component) => $component->getId() === 'schema-form')
                                            ?->getChildComponentContainer()
                                            ->fill($block?->is_fixed_content ? $block->data : []);
                                    }),
                                SchemaFormBuilder::make('data')
                                    ->id('schema-form')
                                    ->dehydrated(fn (\Filament\Forms\Get $get) => ! (self::getCachedBlocks()->firstWhere('id', $get('block_id'))?->is_fixed_content))
                                    ->disabled(fn (\Filament\Forms\Get $get) => self::getCachedBlocks()->firstWhere('id', $get('block_id'))?->is_fixed_content ?? false)
                                    ->schemaData(fn (\Filament\Forms\Get $get) => self::getCachedBlocks()->firstWhere('id', $get('block_id'))?->blueprint->schema),
                            ]),
                    ])->columnSpan(2),
                MetaDataForm::make('Meta Data')
                    ->columnSpan(1),
            ]);
    }

    /** @throws Exception */
    #[\Override]
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(query: fn (Builder $query, string $search): Builder =>
                        /** @var Builder|Page $query */
                        $query->Where('name', 'like', "%{$search}%"))
                    ->lineClamp(1)
                    ->wrap(),
                Tables\Columns\TextColumn::make('name')
                    ->hidden()
                    ->searchable()
                    ->lineClamp(1)
                    ->wrap(),
                Tables\Columns\TextColumn::make('activeRouteUrl.url')
                    ->label('URL')
                    ->sortable()
                    ->searchable()
                    ->lineClamp(1)
                    ->wrap(),
                Tables\Columns\TextColumn::make('locale')
                    ->searchable()
                    ->hidden(TenantFeatureSupport::inactive(Internationalization::class)),
                Tables\Columns\TextColumn::make('visibility')
                    ->badge()
                    ->formatStateUsing(fn (Visibility $state) => Str::headline($state->value))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('sites.name')
                    ->badge()
                    ->hidden((bool) ! (TenantFeatureSupport::active(SitesManagement::class)))
                    ->toggleable(condition: fn () => TenantFeatureSupport::active(SitesManagement::class),
                        isToggledHiddenByDefault: fn () => TenantFeatureSupport::inactive(SitesManagement::class)),
                Tables\Columns\IconColumn::make('published_at')
                    ->label(trans('Published'))
                    ->icons([
                        'heroicon-o-check-circle' => fn ($state) => $state !== null,
                        'heroicon-o-x-circle' => fn ($state) => $state === null,
                    ])
                    ->color(fn ($state) => $state !== null ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('author.full_name')
                    ->sortable(['first_name', 'last_name'])
                    ->searchable(query: fn (Builder $query, string $search): Builder =>
                        /** @var Builder|Page $query */
                        $query->whereHas('author', function ($query) use ($search) {
                            $query->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        })),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
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
                    ->hidden((bool) ! (\Domain\Tenant\TenantFeatureSupport::active(\App\Features\CMS\SitesManagement::class)))
                    ->relationship('sites', 'name', function (Builder $query) {

                        if (filament_admin()->can('site.siteManager') &&
                        ! (filament_admin()->hasRole(config()->string('domain.role.super_admin')))) {
                            return $query->whereIn('id', filament_admin()->userSite->pluck('id')->toArray());
                        }

                        return $query;

                    }),
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
    #[\Override]
    public static function getEloquentQuery(): Builder
    {
        if (filament_admin()->hasRole(config()->string('domain.role.super_admin'))) {
            return static::getModel()::query();
        }

        if (TenantFeatureSupport::active(SitesManagement::class) &&
            filament_admin()->can('site.siteManager') &&
            /** @phpstan-ignore booleanNot.alwaysTrue */
            ! (filament_admin()->hasRole(config()->string('domain.role.super_admin')))
        ) {
            return static::getModel()::query()->wherehas('sites', fn ($q) => $q->whereIn('site_id', filament_admin()->userSite->pluck('id')->toArray()));
        }

        return static::getModel()::query();

    }

    #[\Override]
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

    #[\Override]
    public static function getRelations(): array
    {
        return [
            ActivitiesRelationManager::class,
            PageTranslationRelationManager::class,
        ];
    }

    #[\Override]
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

            self::$cachedBlocks = Block::with(['media', 'sites'])->get();
        }

        return self::$cachedBlocks;
    }
}
