<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use Closure;
use Exception;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Support\Str;
use Domain\Page\Models\Page;
use Domain\Site\Models\Site;
use Filament\Resources\Form;
use Domain\Page\Models\Block;
use Filament\Resources\Table;
use Filament\Resources\Resource;
use App\FilamentTenant\Resources;
use Domain\Page\Enums\Visibility;
use Domain\Page\Models\BlockContent;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Component;
use Domain\Page\Actions\DeletePageAction;
use Illuminate\Database\Eloquent\Builder;
use App\FilamentTenant\Support\MetaDataForm;
use Illuminate\Database\Eloquent\Collection;
use App\FilamentTenant\Support\RouteUrlFieldset;
use App\FilamentTenant\Support\SchemaFormBuilder;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Support\ConstraintsRelationships\Exceptions\DeleteRestrictedException;
use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use Support\RouteUrl\Rules\MicroSiteUniqueRouteUrlRule;
use Illuminate\Validation\Rules\Unique;

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
                                    callback: function (Unique $rule) {

                                        if(tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class)) {
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
                            RouteUrlFieldset::make()
                                ->disabled(fn (?Page $record) => $record?->isHomePage()),
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
                                ->rule(fn (?Page $record, Closure $get) => new MicroSiteUniqueRouteUrlRule($record, $get('route_url')))
                                ->options(
                                    fn () => Site::orderBy('name')
                                        ->pluck('name', 'id')
                                        ->toArray()
                                )
                                ->afterStateHydrated(function (Forms\Components\CheckboxList $component, ?Page $record): void {
                                    if ( ! $record) {
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
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable()
                    ->truncate('xs', true),
                Tables\Columns\TextColumn::make('activeRouteUrl.url')
                    ->label('URL')
                    ->sortable()
                    ->searchable()
                    ->truncate('xs', true),
                Tables\Columns\BadgeColumn::make('visibility')
                    ->formatStateUsing(fn ($state) => Str::headline($state))
                    ->sortable()
                    ->searchable(),
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
        if ( ! isset(self::$cachedBlocks)) {
            self::$cachedBlocks = Block::with(['blueprint', 'media'])->get();
        }

        return self::$cachedBlocks;
    }
}
