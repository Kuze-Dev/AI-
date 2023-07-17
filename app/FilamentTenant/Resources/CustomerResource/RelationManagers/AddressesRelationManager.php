<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\CustomerResource\RelationManagers;

use Domain\Address\Actions\CreateAddressAction;
use Domain\Address\Actions\DeleteAddressAction;
use Domain\Address\Actions\UpdateAddressAction;
use Domain\Address\DataTransferObjects\AddressData;
use Domain\Address\Enums\AddressLabelAs;
use Domain\Address\Exceptions\CantDeleteDefaultAddressException;
use Domain\Address\Models\Address;
use Domain\Address\Models\Country;
use Domain\Address\Models\State;
use Exception;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Select::make('country_id')
                    ->label(trans('Country'))
                    ->required()
                    ->preload()
                    ->optionsFromModel(Country::class, 'name')
                    ->reactive()
                    ->afterStateUpdated(function (callable $set) {
                        $set('state_id', null);
                    })
                    ->dehydrated(false),
                Forms\Components\Select::make('state_id')
                    ->label(trans('State'))
                    ->required()
                    ->preload()
                    ->optionsFromModel(
                        State::class,
                        'name',
                        fn (Builder $query, callable $get) => $query->where('country_id', $get('country_id'))
                    )
                    ->reactive(),
                Forms\Components\TextInput::make('zip_code')
                    ->translateLabel()
                    ->required()
                    ->string()
                    ->maxLength(255)
                    ->reactive(),
                Forms\Components\TextInput::make('city')
                    ->translateLabel()
                    ->required()
                    ->string()
                    ->maxLength(255),
                Forms\Components\Select::make('label_as')
                    ->translateLabel()
                    ->required()
                    ->options(
                        collect(AddressLabelAs::cases())
                            ->mapWithKeys(fn (AddressLabelAs $target) => [
                                $target->value => Str::headline($target->value),
                            ])
                            ->toArray()
                    )
                    ->enum(AddressLabelAs::class)
                    ->columnSpanFull(),
                Forms\Components\Checkbox::make('is_default_billing')
                    ->translateLabel(),
                Forms\Components\Checkbox::make('is_default_shipping')
                    ->translateLabel(),
            ])->columns(2);
    }

    /** @throws Exception */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('label_as')
                    ->translateLabel()
                    ->sortable()
                    ->searchable()
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('address_line_1')
                    ->translateLabel()
                    ->sortable()
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('state.country.name')
                    ->translateLabel()
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('state.name')
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('city')
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
                    ->using(function (self $livewire, array $data) {

                        $data['customer_id'] = $livewire->getOwnerRecord()->getKey();

                        return DB::transaction(
                            fn () => app(CreateAddressAction::class)
                                ->execute(AddressData::fromArray($data))
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->translateLabel()
                    ->mutateRecordDataUsing(function (array $data, Address $record): array {
                        $data['country_id'] = $record->state->country_id;

                        return $data;
                    })
                    ->using(function (self $livewire, Address $record, array $data) {
                        $data['customer_id'] = $livewire->getOwnerRecord()->getKey();

                        return DB::transaction(
                            fn () => DB::transaction(
                                fn () => app(UpdateAddressAction::class)
                                    ->execute($record, AddressData::fromArray($data))
                            )
                        );
                    }),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DeleteAction::make()
                        ->translateLabel()
                        ->using(function (Address $record) {
                            try {
                                return app(DeleteAddressAction::class)->execute($record);
                            } catch (CantDeleteDefaultAddressException $e) {
                                Filament::notify('danger', trans('Deleting default address not allowed.'));

                                return false;
                            } catch (DeleteRestrictedException $e) {
                                return false;
                            }
                        }),
                ]),
            ]);
    }
}
