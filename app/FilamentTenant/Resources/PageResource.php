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
use Domain\Page\Models\Page;
use Domain\Page\Models\Block;
use Domain\Page\Models\BlockContent;
use Exception;
use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Filters\Layout;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

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
                                ->unique(ignoreRecord: true)
                                ->lazy()
                                ->afterStateUpdated(function (Forms\Components\TextInput $component) {
                                    $component->getContainer()
                                        ->getComponent(fn (Component $component) => $component->getId() === 'route_url')
                                        ?->dispatchEvent('route_url::update');
                                })
                                ->required(),
                            RouteUrlFieldset::make(),
                            Forms\Components\Hidden::make('author_id')
                                ->default(Auth::id()),
                        ]),
                        Forms\Components\Section::make(trans('Blocks'))
                            ->schema([
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
                                    ->disableLabel()
                                    ->minItems(1)
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
                    ->searchable(),
                Tables\Columns\TextColumn::make('activeRouteUrl.url')
                    ->label('URL')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('author.full_name')
                    ->sortable()
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
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
            ])
            ->filters([])
            ->filtersLayout(Layout::AboveContent)
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DeleteAction::make(),
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
