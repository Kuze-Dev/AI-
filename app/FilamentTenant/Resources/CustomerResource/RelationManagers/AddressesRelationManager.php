<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\CustomerResource\RelationManagers;

use Domain\Address\Actions\CreateAddressAction;
use Domain\Address\Actions\DeleteAddressAction;
use Domain\Address\Actions\UpdateAddressAction;
use Domain\Address\DataTransferObjects\AddressData;
use Domain\Address\Enums\CountryStateOrRegion;
use Domain\Address\Models\Address;
use Domain\Address\Models\City;
use Domain\Address\Models\Country;
use Domain\Address\Models\Region;
use Domain\Address\Models\State;
use Exception;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Support\ConstraintsRelationships\Exceptions\DeleteRestrictedException;

class AddressesRelationManager extends RelationManager
{
    protected static string $relationship = 'addresses';

    protected static ?string $recordTitleAttribute = 'full_detail';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('address_line_1')
                    ->translateLabel()
                    ->required()
                    ->string()
                    ->maxLength(255),
                Forms\Components\TextInput::make('address_line_2')
                    ->translateLabel()
                    ->nullable()
                    ->string()
                    ->maxLength(255),

                Forms\Components\Select::make('country_id')
                    ->label(trans('Country'))
                    ->required()
                    ->preload()
                    ->optionsFromModel(Country::class, 'name')
                    ->reactive()
                    ->afterStateHydrated(function (callable $set) {
                        $set('state_id', null);
                        $set('region_id', null);
                        $set('city_id', null);
                        $set('zip_code', null);
                    }),
                Forms\Components\Select::make('state_id')
                    ->label(trans('State'))
                    ->required()
                    ->preload()
                    ->optionsFromModel(State::class, 'name')
                    ->reactive()
                    ->visible(function (callable $get) {
                        if ($get('country_id') === null) {
                            return false;
                        }

                        return Country::whereKey($get('country_id'))->value('state_or_region') === CountryStateOrRegion::STATE;
                    }),
                Forms\Components\Select::make('region_id')
                    ->label(trans('Region'))
                    ->required()
                    ->preload()
                    ->optionsFromModel(Region::class, 'name')
                    ->reactive()
                    ->visible(function (callable $get) {
                        if ($get('country_id') === null) {
                            return false;
                        }

                        return Country::whereKey($get('country_id'))->value('state_or_region') === CountryStateOrRegion::REGION;
                    }),
                Forms\Components\Select::make('city_id')
                    ->label(trans('City'))
                    ->required()
                    ->preload()
                    ->optionsFromModel(City::class, 'name', function (Builder $query, callable $get) {
                        /** @var \Illuminate\Database\Eloquent\Builder|\Domain\Address\Models\City $query */
                        if ($get('state_id') !== null) {
                            return $query->where('state_id', $get('state_id'));
                        } elseif ($get('region_id') !== null) {
                            return $query->where('region_id', $get('region_id'));
                        }

                        return $query->whereKey(0); // no result
                    })
                    ->reactive()
                    ->visible(function (callable $get) {
                        return $get('state_id') !== null || $get('region_id') !== null;
                    }),
                Forms\Components\TextInput::make('zip_code')
                    ->translateLabel()
                    ->required()
                    ->string()
                    ->maxLength(255)
                    ->visible(function (callable $get) {
                        return $get('city_id') !== null;
                    }),

                Forms\Components\Checkbox::make('is_default_billing')
                    ->translateLabel(),
                Forms\Components\Checkbox::make('is_default_shipping')
                    ->translateLabel(),

            ])->columns(1);
    }

    /** @throws Exception */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('address_line_1')
                    ->translateLabel()
                    ->sortable()
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('address_line_2')
                    ->translateLabel()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrap(),
                Tables\Columns\TextColumn::make('country') // TODO: relation
                    ->translateLabel()
                    ->sortable()
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('state_or_region') // TODO: relation
                    ->label(trans('State/Region'))
                    ->sortable()
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('city_or_province') // TODO: relation
                    ->label(trans('City/Province'))
                    ->sortable()
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('zip_code')
                    ->translateLabel()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrap(),
                Tables\Columns\IconColumn::make('is_default_billing')
                    ->translateLabel()
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_default_shipping')
                    ->translateLabel()
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_default_billing')
                    ->translateLabel(),
                Tables\Filters\TernaryFilter::make('is_default_shipping')
                    ->translateLabel(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->translateLabel()
                    ->using(function (array $data, self $livewire) {

                        $data['customer_id'] = $livewire->getOwnerRecord()->getKey();

                        return DB::transaction(
                            fn () => app(CreateAddressAction::class)
                                ->execute(new AddressData(...$data))
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->translateLabel()
                    ->using(fn (Address $record, array $data) => DB::transaction(
                        fn () => DB::transaction(
                            fn () => app(UpdateAddressAction::class)
                                ->execute($record, new AddressData(...$data))
                        )
                    )),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DeleteAction::make()
                        ->translateLabel()
                        ->using(function (Address $record) {
                            try {
                                return app(DeleteAddressAction::class)->execute($record);
                            } catch (DeleteRestrictedException $e) {
                                return false;
                            }
                        }),
                ]),
            ]);
    }
}
