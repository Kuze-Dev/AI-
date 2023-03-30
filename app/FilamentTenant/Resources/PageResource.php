<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources;
use App\FilamentTenant\Support\MetaDataForm;
use App\FilamentTenant\Support\SchemaFormBuilder;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Closure;
use Domain\Page\Models\Page;
use Domain\Page\Models\Block;
use Domain\Page\Models\BlockContent;
use Exception;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Filters\Layout;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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
                                ->afterStateUpdated(function (Closure $get, Closure $set, $state) {
                                    if ($get('slug') === Str::slug($state) || blank($get('slug'))) {
                                        $set('slug', Str::slug($state));
                                    }
                                })
                                ->required(),
                            Forms\Components\TextInput::make('slug')
                                ->unique(ignoreRecord: true)
                                ->dehydrateStateUsing(fn (Closure $get, $state) => Str::slug($state ?: $get('name'))),
                            Forms\Components\TextInput::make('route_url')
                                ->required()
                                ->helperText('Use "{{ $slug }}" to insert the current slug.'),
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
                    ->columnSpan(1)
                    ->extraAttributes(['class' => 'md:sticky top-[5.5rem]']),
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
                Tables\Columns\TextColumn::make('slug')
                    ->sortable()
                    ->searchable(),
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

    /** @return Collection<int, Block> $cachedBlocks */
    protected static function getCachedBlocks(): Collection
    {
        if ( ! isset(self::$cachedBlocks)) {
            self::$cachedBlocks = Block::with(['blueprint', 'media'])->get();
        }

        return self::$cachedBlocks;
    }
}
