<?php

namespace App\FilamentTenant\Resources;

use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources\MenuResource\Pages;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Closure;
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

    protected static ?string $navigationGroup = 'System';

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

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
                    Forms\Components\Card::make([
                        Forms\Components\Repeater::make('nodes')
                            ->label('Menus')
                            ->relationship()
                            ->collapsible()
                            ->orderable()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Label')
                                    ->required()
                                    ->maxLength(100),
                                Forms\Components\Select::make('target')
                                    ->options([
                                        '_blank' => '_blank',
                                        '_self' => '_self',
                                        '_parent' => '_parent',
                                    ]),
                                Forms\Components\TextInput::make('url')
                                    ->url()
                                    ->placeholder('https://example.com')
                                    ->columnSpanFull(),
                                Forms\Components\Repeater::make('childs')
                                    ->label('Sub Menus')
                                    ->relationship()
                                    ->collapsible()
                                    ->orderable()
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Label')
                                            ->required()
                                            ->maxLength(100),
                                        Forms\Components\Select::make('target')
                                            ->options([
                                                '_blank' => '_blank',
                                                '_self' => '_self',
                                                '_parent' => '_parent',
                                            ]),
                                        Forms\Components\TextInput::make('url')
                                            ->url()
                                            ->placeholder('https://example.com')
                                            ->columnSpanFull(),
                                        Forms\Components\Repeater::make('childs')
                                            ->label('Sub Menus')
                                            ->relationship()
                                            ->collapsible()
                                            ->orderable()
                                            ->schema([
                                                Forms\Components\TextInput::make('name')
                                                    ->label('Label')
                                                    ->required()
                                                    ->maxLength(100),
                                                Forms\Components\Select::make('target')
                                                    ->options([
                                                        '_blank' => '_blank',
                                                        '_self' => '_self',
                                                        '_parent' => '_parent',
                                                    ]),
                                                Forms\Components\TextInput::make('url')
                                                    ->url()
                                                    ->placeholder('https://example.com')
                                                    ->columnSpanFull()
                                            ])
                                            ->columns(2)
                                            ->columnSpan(2)
                                    ])
                                    ->columns(2)
                                    ->columnSpan(2)
                            ])
                            ->columns(2)
                            ->columnSpan(2)
                    ])
                ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function () {
                        $nodes = $this->record->nodes;

                        foreach ($nodes as $node);
                        Node::find($node->id)->delete();
                    }),
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
