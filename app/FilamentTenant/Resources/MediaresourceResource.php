<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\FilamentTenant\Resources\MediaresourceResource\Pages;
use App\Filament\Resources\MediaresourceResource\RelationManagers;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaresourceResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = Media::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

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
        ->contentGrid([
            'sm' => 2,
            'md' => 3,
            'xl' => 4,
        ])
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\ImageColumn::make('original_url')
                    ->size('100%')
                    ->extraAttributes(['class' => ' rounded-lg w-full overflow-hidden bg-neutral-800 pb-8'])
                    ->extraImgAttributes(['class' => 'aspect-[5/3] object-contain']),
                Tables\Columns\TextColumn::make('name')
                    ->url(fn (Media $record): string => '/admin')
                    ->openUrlInNewTab()
                    ->extraAttributes(['class' => ' rounded-lg w-full overflow-hidden'])
                    ->translateLabel()
                    ->searchable()
                    ->sortable(),
               
                ])->space(2),
                  
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListMediaresources::route('/'),
            'create' => Pages\CreateMediaresource::route('/create'),
            'edit' => Pages\EditMediaresource::route('/{record}/edit'),
        ];
    }    
}
