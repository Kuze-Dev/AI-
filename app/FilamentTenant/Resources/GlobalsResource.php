<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Support\SchemaFormBuilder;
use Domain\Blueprint\Models\Blueprint;
use App\FilamentTenant\Resources\GlobalsResource\Pages\CreateGlobals;
use App\FilamentTenant\Resources\GlobalsResource\Pages\EditGlobals;
use App\FilamentTenant\Resources\GlobalsResource\Pages\ListGlobals;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Domain\Globals\Models\Globals;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\Facades\Auth;
use Closure;
use Illuminate\Support\Str;

class GlobalsResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = Globals::class;

    protected static ?string $navigationGroup = 'CMS';

    protected static ?string $navigationIcon = 'heroicon-o-globe';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 9;

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
                    ->disabled(fn (?Globals $record) => $record !== null),

                SchemaFormBuilder::make('data')
                    ->id('schema-form')
                    ->schemaData(fn (Closure $get) => ($get('blueprint_id') != null) ? Blueprint::whereId($get('blueprint_id'))->first()?->schema : null),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('blueprint.name')
                    ->sortable()
                    ->searchable()
                    ->url(fn (Globals $record) => BlueprintResource::getUrl('edit', $record->blueprint)),
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
            'index' => ListGlobals::route('/'),
            'create' => CreateGlobals::route('/create'),
            'edit' => EditGlobals::route('/{record}/edit'),
        ];
    }
}
