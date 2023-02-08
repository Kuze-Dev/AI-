<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources;
use App\FilamentTenant\Support\SchemaFormBuilder;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Closure;
use Domain\Page\Models\Page;
use Domain\Page\Models\Slice;
use Domain\Page\Models\SliceContent;
use Domain\Support\MetaTag\MetaTagsForm;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\Facades\Auth;
use Exception;
use Filament\Forms\Components\Component;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class PageResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = Page::class;

    protected static ?string $navigationGroup = 'CMS';

    protected static ?string $navigationIcon = 'heroicon-o-document';

    protected static ?string $recordTitleAttribute = 'name';

    /** @var Collection<int, Slice> */
    public static ?Collection $cachedSlices = null;

    public static function form(Form $form): Form
    {
        return $form->schema([
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
            Forms\Components\Section::make(trans('Slices'))
                ->schema([
                    Forms\Components\Repeater::make('slice_contents')
                        ->afterStateHydrated(function (Forms\Components\Repeater $component, ?Page $record, ?array $state) {
                            if ($record === null || $record->sliceContents->isEmpty()) {
                                $component->state($state ?? []);

                                return;
                            }

                            $component->state(
                                $record->sliceContents->sortBy('order')
                                    ->mapWithKeys(fn (SliceContent $item) => ["record-{$item->getKey()}" => $item])
                                    ->toArray()
                            );

                            // WORKAROUND: Force after state hydrate after setting the new state
                            foreach ($component->getChildComponentContainers() as $componentContainer) {
                                $componentContainer->callAfterStateHydrated();
                            }
                        })
                        ->itemLabel(fn (array $state) => self::getCachedSlices()->firstWhere('id', $state['slice_id'])?->name)
                        ->disableLabel()
                        ->minItems(1)
                        ->collapsible()
                        ->orderable('order')
                        ->schema([
                            Forms\Components\Select::make('slice_id')
                                ->label('Slice')
                                ->options(
                                    self::getCachedSlices()
                                        ->sortBy('name')
                                        ->pluck('name', 'id')
                                        ->toArray()
                                )
                                ->hidden(fn (?Page $record, Closure $get) => $record && $record->sliceContents->firstWhere('id', $get('id')))
                                ->required()
                                ->exists(Slice::class, 'id')
                                ->searchable()
                                ->reactive()
                                ->afterStateUpdated(
                                    fn (Forms\Components\Select $component) => $component->getContainer()
                                        ->getComponent(fn (Component $component) => $component->getId() === 'schema-form')
                                        ?->getChildComponentContainer()
                                        ->fill()
                                )
                                ->dehydrateStateUsing(fn (string|int $state) => (int) $state),
                            SchemaFormBuilder::make('data')
                                ->id('schema-form')
                                ->schemaData(fn (Closure $get) => self::getCachedSlices()->firstWhere('id', $get('slice_id'))?->blueprint->schema),
                        ]),
                ]),
            MetaTagsForm::formBuilder(),
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

    /** @return Collection<int, Slice> $cachedSlices */
    protected static function getCachedSlices(): Collection
    {
        if ( ! isset(self::$cachedSlices)) {
            self::$cachedSlices = Slice::with('blueprint')->get();
        }

        return self::$cachedSlices;
    }
}
