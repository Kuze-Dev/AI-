<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use Closure;
use Filament\Forms;
use Filament\Tables;
use Domain\Site\Models\Site;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Resources\Resource;
use Domain\Globals\Models\Globals;
use Illuminate\Support\Facades\Auth;
use Domain\Blueprint\Models\Blueprint;
use App\FilamentTenant\Support\SchemaFormBuilder;
use App\FilamentTenant\Resources\GlobalsResource\Pages\EditGlobals;
use App\FilamentTenant\Resources\GlobalsResource\Pages\ListGlobals;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use App\FilamentTenant\Resources\GlobalsResource\Pages\CreateGlobals;
use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;

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
                    ->required()
                    ->string()
                    ->maxLength(255),
                Forms\Components\Select::make('blueprint_id')
                    ->label(trans('Blueprint'))
                    ->required()
                    ->preload()
                    ->optionsFromModel(Blueprint::class, 'name')
                    ->disabled(fn (?Globals $record) => $record !== null)
                    ->reactive(),
                Forms\Components\Card::make([
                    Forms\Components\CheckboxList::make('sites')
                        ->options(
                            fn () => Site::orderBy('name')
                                ->pluck('name', 'id')
                                ->toArray()
                        )
                        ->afterStateHydrated(function (Forms\Components\CheckboxList $component, ?Globals $record): void {
                            if ( ! $record) {
                                $component->state([]);

                                return;
                            }

                            $component->state(
                                $record->sites->pluck('id')
                                    ->intersect(array_keys($component->getOptions()))
                                    ->values()
                                    ->toArray()
                            );
                        }),
                ]),
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
                    ->searchable()
                    ->truncate('max-w-xs xl:max-w-md 2xl:max-w-2xl', true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('blueprint')
                    ->relationship('blueprint', 'name')
                    ->searchable()
                    ->optionsLimit(20),
            ])

            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DeleteAction::make(),
                ]),
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
