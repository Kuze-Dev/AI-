<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\FilamentTenant\Resources\ServiceOrderResource\Pages\CreateServiceOrder;
use App\FilamentTenant\Resources\ServiceOrderResource\Pages\EditServiceOrder;
use App\FilamentTenant\Resources\ServiceOrderResource\Pages\ListServiceOrder;
use App\FilamentTenant\Support\TextLabel;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Domain\Customer\Models\Customer;
use Domain\Service\Models\Service;
use Domain\ServiceOrder\Models\ServiceOrder;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class ServiceOrderResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = ServiceOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    protected static ?string $navigationGroup = 'Service Management';

    public static function form(Form $form): Form
    {

        return $form
            ->columns(3)
            ->schema([
                Forms\Components\Group::make()->schema([
                    Section::make(trans('Customer'))
                        ->schema([
                            Forms\Components\Select::make('customer_id')
                                ->label(trans(''))
                                ->placeholder(trans('Select Customer'))
                                ->required()
                                ->preload()
                                ->optionsFromModel(Customer::class, 'email')
                                ->disabled(fn (?Customer $record) => $record !== null)
                                ->reactive(),

                            Forms\Components\Group::make()->columns(2)->schema([
                                Placeholder::make('First Name')
                                    ->content('Jerome'),
                                Placeholder::make('Last Name')
                                    ->content('Hipolito'),
                                Placeholder::make('Email')
                                    ->content('jerome@halcyon.com'),
                                Placeholder::make('Mobile')
                                    ->content('09123456789'),
                                Placeholder::make('Service Address')
                                    ->content('123 abc street')->columnSpan(2),
                                Placeholder::make('Billing Address')
                                    ->content('123 abc street')->columnSpan(2),
                            ])->visible(
                                function (array $state) {
                                    return isset($state['customer_id']);
                                }
                            ),

                        ]),
                    Section::make(trans('Service'))
                        ->schema([
                            Forms\Components\Group::make()->columns(2)->schema([
                                Forms\Components\Select::make('service_id')
                                    ->label(trans('Select Service'))
                                    ->placeholder(trans('Select Service'))
                                    ->required()
                                    ->preload()
                                    ->optionsFromModel(Service::class, 'name')
                                    ->disabled(fn (?Service $record) => $record !== null),

                                DateTimePicker::make('schedule'),

                                Forms\Components\Group::make()->columnSpan(2)->schema([
                                    Forms\Components\Fieldset::make('')->schema([
                                        Placeholder::make('Service')
                                            ->content('Sample subscription'),
                                        Placeholder::make('Service Price')
                                            ->content('$1000'),
                                        Placeholder::make('Billing Schedule')
                                            ->content('Every Xth of the month'),
                                        Placeholder::make('Due Date')
                                            ->content('Every Xth of the month'),
                                    ]),
                                ]),
                                // ->visible(
                                //     function (array $state) {
                                //         return isset($state['service_id']);
                                //     }
                                // )

                                TextLabel::make('')
                                    ->label(trans('Additional Charges'))
                                    ->alignLeft()
                                    ->size('xl')
                                    ->weight('bold')
                                    ->inline()
                                    ->readOnly(),

                                Repeater::make('additional_charges')
                                    ->label('')
                                    ->createItemButtonLabel('Additional Charges')
                                    ->columnSpan(2)
                                    ->schema([
                                        TextInput::make('name'),
                                        TextInput::make('quantity')->numeric(),
                                        TextInput::make('price')->numeric(),
                                    ])
                                    ->columns(3),
                            ]),
                        ]),
                    Section::make(trans('Form Title'))
                        ->schema([]),
                ])->columnSpan(2),

                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make('Summary')
                        ->columns(2)
                        ->translateLabel()
                        ->schema([
                            TextLabel::make('')
                                ->label(trans('Service Price'))
                                ->alignLeft()
                                ->size('md')
                                ->inline()
                                ->readOnly(),
                            TextLabel::make('')
                                ->label(trans('$1000.00'))
                                ->alignLeft()
                                ->size('md')
                                ->inline()
                                ->readOnly(),
                            TextLabel::make('')
                                ->label(trans('Additional Charges'))
                                ->alignLeft()
                                ->size('md')
                                ->inline()
                                ->readOnly(),
                            TextLabel::make('')
                                ->label(trans('$1000.00'))
                                ->alignLeft()
                                ->size('md')
                                ->inline()
                                ->readOnly(),
                            TextLabel::make('')
                                ->label(trans('Total'))
                                ->alignLeft()
                                ->size('md')
                                ->inline()
                                ->readOnly()
                                ->color('warning'),
                            TextLabel::make('')
                                ->label(trans('$2000.00'))
                                ->alignLeft()
                                ->size('md')
                                ->inline()
                                ->readOnly()
                                ->color('warning'),
                        ]),

                ])->columnSpan(1),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServiceOrder::route('/'),
            'create' => CreateServiceOrder::route('/create'),
            'edit' => EditServiceOrder::route('/{record}/edit'),
        ];
    }
}
