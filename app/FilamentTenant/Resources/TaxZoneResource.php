<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources\TaxZoneResource\Pages;
use Domain\Address\Models\Country;
use Domain\Address\Models\State;
use Domain\Taxation\Enums\PriceDisplay;
use Domain\Taxation\Enums\TaxZoneType;
use Domain\Taxation\Models\TaxZone;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class TaxZoneResource extends Resource
{
    protected static ?string $model = TaxZone::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string
    {
        return trans('Shop Configuration');
    }

    public static function getNavigationLabel(): string
    {
        return trans('Tax Zone');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->string()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),
                    Forms\Components\Select::make('price_display')
                        ->options([
                            PriceDisplay::INCLUSIVE->value => trans('Tax Incuded in total'),
                            PriceDisplay::EXCLUSIVE->value => trans('Tax Excluded in total'),
                        ])
                        ->required()
                        ->enum(PriceDisplay::class),
                    Forms\Components\Group::make([
                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->columnSpan(1),
                        Forms\Components\Toggle::make('is_default')
                            ->default(fn () => ! TaxZone::whereIsDefault(true)->exists())
                            ->columnSpan(1),
                    ])->columns(2),
                    Forms\Components\Select::make('type')
                        ->options([
                            TaxZoneType::COUNTRY->value => trans('Limit by Countries'),
                            TaxZoneType::STATE->value => trans('Limit by States/Provinces'),
                        ])
                        ->required()
                        ->enum(TaxZoneType::class)
                        ->reactive(),

                    Forms\Components\Group::make([
                        Forms\Components\Fieldset::make(trans('Countries'))
                            ->schema([
                                Forms\Components\CheckboxList::make('countries')
                                    ->options(Country::pluck('name', 'id'))
                                    ->formatStateUsing(fn (?TaxZone $record) => $record?->countries->modelKeys() ?? [])
                                    ->bulkToggleable()
                                    ->searchable()
                                    ->disableLabel()
                                    ->required()
                                    ->columns(3),
                            ])
                            ->columns(1),
                    ])
                        ->visible(function (array $state) {
                            $type = ! $state['type'] instanceof TaxZoneType
                                ? TaxZoneType::tryFrom($state['type'] ?? '')
                                : $state['type'];

                            return $type === TaxZoneType::COUNTRY;
                        }),
                    Forms\Components\Group::make([
                        Forms\Components\Select::make('countries')
                            ->label(trans('Country'))
                            ->afterStateUpdated(fn (\Filament\Forms\Set $set) => $set('states', []))
                            ->optionsFromModel(Country::class, 'name')
                            ->preload()
                            ->dehydrateStateUsing(fn ($state) => is_array($state) ? $state : [$state])
                            ->required()
                            ->reactive(),
                        Forms\Components\Fieldset::make(trans('States/Provinces'))
                            ->schema([
                                Forms\Components\CheckboxList::make('states')
                                    ->options(
                                        fn (\Filament\Forms\Get $get) => ($country = $get('countries'))
                                            ? State::whereCountryId($country)->pluck('name', 'id')
                                            : []
                                    )
                                    ->formatStateUsing(fn (?TaxZone $record) => $record?->states->modelKeys() ?? [])
                                    ->bulkToggleable()
                                    ->searchable()
                                    ->disableLabel()
                                    ->required()
                                    ->columns(3),
                            ])
                            ->columns(1),
                    ])
                        ->visible(function (array $state) {
                            $type = ! $state['type'] instanceof TaxZoneType
                                ? TaxZoneType::tryFrom($state['type'] ?? '')
                                : $state['type'];

                            return $type === TaxZoneType::STATE;
                        }),

                    Forms\Components\TextInput::make('percentage')
                        ->required()
                        ->numeric()
                        ->visible(fn (\Filament\Forms\Get $get) => filled($get('type')))
                        ->dehydrateStateUsing(fn (string|int|null $state) => filled($state) ? (int) $state : null),
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
                Tables\Columns\TextColumn::make('percentage')
                    ->formatStateUsing(fn (float $state) => $state.'%')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->formatStateUsing(fn (TaxZone $record) => match ($record->type) {
                        TaxZoneType::COUNTRY => trans('Limit by Countries'),
                        TaxZoneType::STATE => trans('Limit by States/Provinces'),
                    })
                    ->sortable()
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_default')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([]);
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
            'index' => Pages\ListTaxZones::route('/'),
            'create' => Pages\CreateTaxZone::route('/create'),
            'edit' => Pages\EditTaxZone::route('/{record}/edit'),
        ];
    }
}
