<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources\TaxZoneResource\Pages;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Closure;
use Domain\Taxation\Enums\PriceDisplay;
use Domain\Taxation\Enums\TaxZoneType;
use Domain\Taxation\Models\TaxZone;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\Facades\Auth;

class TaxZoneResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = TaxZone::class;

    protected static ?string $navigationGroup = 'eCommerce';

    protected static ?string $navigationIcon = 'heroicon-o-receipt-tax';

    protected static ?string $recordTitleAttribute = 'name';

    protected static function getNavigationLabel(): string
    {
        return trans('Taxation');
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
                                    ->bulkToggleable()
                                    ->searchable()
                                    ->disableLabel()
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
                        Forms\Components\Select::make('country'),
                        Forms\Components\Fieldset::make(trans('States/Provinces'))
                            ->schema([
                                Forms\Components\CheckboxList::make('states')
                                    ->bulkToggleable()
                                    ->searchable()
                                    ->disableLabel()
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
                        ->visible(fn (Closure $get) => filled($get('type'))),
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
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_default')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->formatStateUsing(fn (TaxZone $record) => match ($record->type) {
                        TaxZoneType::COUNTRY => trans('Limit by Countries'),
                        TaxZoneType::STATE => trans('Limit by States/Provinces'),
                    })
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('percentage')
                    ->formatStateUsing(fn (float $state) => $state . '%')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->sortable(),
            ])
            ->filters([

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([

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
            'index' => Pages\ListTaxZones::route('/'),
            'create' => Pages\CreateTaxZone::route('/create'),
            'edit' => Pages\EditTaxZone::route('/{record}/edit'),
        ];
    }
}
