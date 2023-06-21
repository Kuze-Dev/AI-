<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources;
use App\FilamentTenant\Support\SchemaFormBuilder;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Domain\Blueprint\Models\Blueprint;
use Domain\Page\Models\Currency;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Filters\Layout;
use Exception;
use Closure;
use Domain\Page\Actions\DeleteBlockAction;
use Domain\Support\ConstraintsRelationships\Exceptions\DeleteRestrictedException;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Illuminate\Support\Facades\Auth;
use App\FilamentTenant\Resources\CurrencyResource\Pages;

class CurrencyResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = Currency::class;

    protected static ?string $navigationGroup = 'eCommerce';

    protected static ?string $navigationIcon = 'heroicon-o-template';

    protected static ?string $recordTitleAttribute = 'name';


    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Card::make([
                Forms\Components\TextInput::make('code')
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\Toggle::make('enabled'),
                Forms\Components\TextInput::make('exchange_rate'),
                Forms\Components\Toggle::make('default'),
                Forms\Components\DateTimePicker::make('created_at')
                    ->disabled()
                    ->default(now()),
                Forms\Components\DateTimePicker::make('updated_at')
                    ->disabled()
                    ->default(now()),
            ]),
        ]);
    }

    /** @throws Exception */
    /** @throws Exception */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                // Tables\Columns\CheckboxColumn::make('enabled')->label(""),
                Tables\Columns\TextColumn::make('code')
                    ->label('Currency')
                    ->sortable()
                    ->searchable(),
                    Tables\Columns\TextColumn::make('exchange_rate')
                    ->label('Exchange Rate')
                    ->sortable()
                    ->searchable(),
                    Tables\Columns\BadgeColumn::make('enabled')
                    ->enum([
                        '1' => 'Selected',
                        '0' => 'Not Selected',
                    ])
                    ->label('Status')
                    ->color(static function ($state): string {
                        if ($state == '1') {
                            return 'success';
                        }
                 
                        return 'secondary';
                    }),
          
                  

            ])
            
            ->filters([])
            ->filtersLayout(Layout::AboveContent)
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DeleteAction::make()
                        ->using(function (Currency $record) {
                            try {
                                return app(DeleteBlockAction::class)->execute($record);
                            } catch (DeleteRestrictedException $e) {
                                return false;
                            }
                        }),
                ]),
            ])
            ->bulkActions([])
            ->defaultSort('id', 'asc');
    }






    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCurrency::route('/'),
            // 'create' => Pages\CreateBlueprint::route('/create'),
            // 'edit' => Pages\EditBlueprint::route('/{record}/edit'),
        ];
    }
}