<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Domain\Blueprint\Models\Blueprint;
use Domain\Page\Models\Slice;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\Facades\Auth;
use Exception;

class SliceResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = Slice::class;

    protected static ?string $navigationGroup = 'CMS';

    protected static ?string $navigationIcon = 'heroicon-o-template';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Card::make([
                Forms\Components\TextInput::make('name')
                    ->unique(ignoreRecord: true)
                    ->required(),
                Forms\Components\TextInput::make('component')
                    ->unique(ignoreRecord: true)
                    ->required(),
                Forms\Components\Select::make('blueprint_id')
                    ->options(
                        fn () => Blueprint::orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray()
                    )
                    ->required()
                    ->exists(Blueprint::class, 'id')
                    ->searchable()
                    ->preload()
                    ->disabled(fn (?Slice $record) => $record !== null),
            ]),
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
                Tables\Columns\TextColumn::make('component')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('blueprint.name')
                    ->sortable()
                    ->searchable()
                    ->url(fn (Slice $record) => BlueprintResource::getUrl('edit', $record->blueprint)),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('blueprint')
                    ->relationship('blueprint', 'name')
                    ->searchable()
                    ->optionsLimit(20),
            ])
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
            'index' => Resources\SliceResource\Slices\ListSlices::route('/'),
            'create' => Resources\SliceResource\Slices\CreateSlice::route('/create'),
            'edit' => Resources\SliceResource\Slices\EditSlice::route('/{record}/edit'),
        ];
    }
}
