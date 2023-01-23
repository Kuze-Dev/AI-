<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources\MenuResource\Pages;
use App\FilamentTenant\Support\Tree;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Closure;
use Domain\Menu\Enums\Target;
use Domain\Menu\Models\Menu;
use Domain\Menu\Models\Node;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class MenuResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = Menu::class;

    protected static ?string $navigationGroup = 'CMS';

    protected static ?string $navigationIcon = 'heroicon-o-menu';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function (Closure $set, $state) {
                            $set('slug', Str::slug($state));
                        }),
                    Forms\Components\TextInput::make('slug')->required()
                        ->disabled(fn (?Menu $record) => $record !== null)
                        ->unique(ignoreRecord: true)
                        ->rules('alpha_dash')
                        ->disabled(),
                ]),
                Forms\Components\Section::make(trans('Nodes'))
                    ->schema([
                        Tree::make('nodes')
                            ->formatStateUsing(
                                fn (?Menu $record, ?array $state) => $record?->nodes
                                    ->mapWithKeys(fn (Node $node) => ["record-{$node->getKey()}" => $node])
                                    ->toArray() ?? $state ?? []
                            )
                            ->itemLabel(fn (array $state) => $state['label'] ?? null)
                            ->schema([
                                Forms\Components\Grid::make(['md' => 4])
                                    ->schema([
                                        Forms\Components\TextInput::make('label')
                                            ->required()
                                            ->maxLength(100)
                                            ->columnSpan(['md' => 3]),
                                        Forms\Components\Select::make('target')
                                            ->options(
                                                collect(Target::cases())
                                                    ->mapWithKeys(fn (Target $target) => [$target->value => Str::headline($target->value)])
                                                    ->toArray()
                                            )
                                            ->columnSpan(['md' => 1]),
                                        Forms\Components\TextInput::make('url')
                                            ->url()
                                            ->placeholder('https://example.com')
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ])
                    ->hiddenOn('create'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
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
            'index' => Pages\ListMenus::route('/'),
            'create' => Pages\CreateMenu::route('/create'),
            'edit' => Pages\EditMenu::route('/{record}/edit'),
        ];
    }
}
