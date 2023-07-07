<?php

namespace App\FilamentTenant\Resources;

use App\FilamentTenant\Resources\ShippingmethodResource\Pages;
use App\Filament\Resources\ShippingmethodResource\RelationManagers;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Domain\ShippingMethod\Models\ShippingMethod;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ShippingmethodResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = ShippingMethod::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShippingmethods::route('/'),
            'create' => Pages\CreateShippingmethod::route('/create'),
            'edit' => Pages\EditShippingmethod::route('/{record}/edit'),
        ];
    }    
}
