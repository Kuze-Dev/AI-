<?php

namespace App\FilamentTenant\Resources;

use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources\MenuResource\Pages;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Domain\Menu\Models\Menu;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\Facades\Auth;

class MenuResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = Menu::class;

    protected static ?string $navigationGroup = 'System';

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make([
                    Forms\Components\TextInput::make('title')
                        ->required(),
                    Forms\Components\Repeater::make('schema')
                        ->label('Menus')
                        ->schema([
                            Forms\Components\TextInput::make('title')
                                ->required(),
                            Forms\Components\TextInput::make('external_link')
                                ->url(),
                            Forms\Components\Repeater::make('child')
                                ->label('Sub-menus')
                                ->schema([
                                    Forms\Components\TextInput::make('title')
                                        ->required(),
                                    Forms\Components\TextInput::make('external_link')
                                        ->url(),
                                    Forms\Components\Repeater::make('child')
                                        ->label('Sub-menus')
                                        ->schema([
                                            Forms\Components\TextInput::make('title')
                                                ->required(),
                                            Forms\Components\TextInput::make('external_link')
                                                ->url(),
                                        ])
                                        ->defaultItems(0)
                                        ->columnSpan(2)
                                        ->collapsible()
                                        ->columns(2)
                                ])
                                ->defaultItems(0)
                                ->columnSpan(2)
                                ->collapsible()
                                ->columns(2)
                        ])
                        ->defaultItems(0)
                        ->collapsible()
                        ->columns(2)
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->sortable(),
            ])
            ->filters([
                //
            ])
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
