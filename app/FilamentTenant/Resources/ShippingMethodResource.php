<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\FilamentTenant\Resources\ShippingmethodResource\Pages;
use Domain\ShippingMethod\Actions\GetAvailableShippingDriverAction;
use Domain\ShippingMethod\Enums\Driver;
use Domain\ShippingMethod\Models\ShippingMethod;
use Exception;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ShippingMethodResource extends Resource
{
    protected static ?string $model = ShippingMethod::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationGroup(): ?string
    {
        return trans('Shop Configuration');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\TextInput::make('title')
                        ->unique(ignoreRecord: true)
                        ->required(),
                    Forms\Components\TextInput::make('subtitle')
                        ->required(),
                    Forms\Components\RichEditor::make('description'),
                    SpatieMediaLibraryFileUpload::make('logo')
                        ->image()
                        ->collection('logo')
                        ->preserveFilenames()
                        ->customProperties(fn (Forms\Get $get) => [
                            'alt_text' => Str::slug($get('title')),
                        ]),
                    Forms\Components\Toggle::make('active')
                        ->label('Status')
                        ->inline(false)
                        ->helperText('If enabled, message here')
                        ->reactive(),
                    Forms\Components\Select::make('driver')
                        ->required()
                        ->options(fn () => app(GetAvailableShippingDriverAction::class)->execute())
                        ->enum(Driver::class)
                        ->reactive(),

                    Forms\Components\Fieldset::make(trans('Ship From Address'))
                        ->schema([
                            Forms\Components\Select::make('shipper_country_id')
                                ->label(trans('Shipper country'))
                                ->required()
                                ->relationship('country', 'name')
                                ->preload()
                                ->searchable()
                                ->reactive()
                                ->afterStateUpdated(function (callable $set) {
                                    $set('shipper_state_id', null);
                                }),
                            Forms\Components\Select::make('shipper_state_id')
                                ->label(trans('Shipper state'))
                                ->required()
                                ->relationship(
                                    'state',
                                    'name',
                                    modifyQueryUsing: fn (Builder $query, Forms\Get $get) => $query
                                        ->where('country_id', $get('shipper_country_id')))
                                ->preload()
                                ->searchable()
                                ->reactive(),
                            Forms\Components\TextInput::make('shipper_address')
                                ->translateLabel()
                                ->required()
                                ->string()
                                ->maxLength(255)
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('shipper_city')
                                ->translateLabel()
                                ->required()
                                ->string()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('shipper_zipcode')
                                ->translateLabel()
                                ->helperText('Please Provide 5 digit zipcode when using US address')
                                ->required()
                                ->string()
                                ->minLength(4)
                                ->maxLength(255),

                        ]),

                ]),
            ]);
    }

    /** @throws Exception */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('subtitle')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('driver')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->sortable(),
            ])
            ->filters([

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShippingMethods::route('/'),
            'create' => Pages\CreateShippingMethod::route('/create'),
            'edit' => Pages\EditShippingMethod::route('/{record}/edit'),
        ];
    }
}
