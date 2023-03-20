<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use Closure;
use Exception;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Form;
use Domain\Page\Models\Slice;
use Filament\Resources\Table;
use Filament\Resources\Resource;
use App\FilamentTenant\Resources;
use Illuminate\Support\Facades\Auth;
use Domain\Blueprint\Models\Blueprint;
use App\FilamentTenant\Support\SchemaFormBuilder;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;

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
                    ->required(),
                Forms\Components\FileUpload::make('image')
                    ->acceptedFileTypes(['image/png', 'image/webp', 'image/jpg', 'image/jpeg'])
                    ->maxSize(1_000),
                Forms\Components\Select::make('blueprint_id')
                    ->options(
                        fn () => Blueprint::orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray()
                    )
                    ->required()
                    ->exists(Blueprint::class, 'id')
                    ->searchable()
                    ->reactive()
                    ->preload()
                    ->disabled(fn (?Slice $record) => $record !== null),
                Forms\Components\Toggle::make('is_fixed_content')
                    ->inline(false)
                    ->hidden(fn (Closure $get) => $get('blueprint_id') ? false : true)
                    ->helperText('If enabled, the content below will serve as the default for all related pages')
                    ->reactive(),
                SchemaFormBuilder::make('data')
                    ->id('schema-form')
                    ->hidden(fn (Closure $get) => $get('is_fixed_content') ? false : true)
                    ->schemaData(fn (Closure $get) => ($get('blueprint_id') != null) ? Blueprint::whereId($get('blueprint_id'))->first()?->schema : null),
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
