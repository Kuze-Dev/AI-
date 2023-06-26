<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use App\FilamentTenant\Resources\DiscountResource\Pages\CreateDiscount;
use App\FilamentTenant\Resources\DiscountResource\Pages\EditDiscount;
use App\FilamentTenant\Resources\DiscountResource\Pages\ListDiscounts;
use Closure;
use Domain\Discount\Actions\AutoGenerateCode;
use Domain\Discount\Models\Discount;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Filters\Layout;
use Str;

class DiscountResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = Discount::class;

    protected static ?string $navigationGroup = 'SHOP CONFIGURATION';

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make([
                    TextInput::make('name')
                        ->reactive()
                        ->afterStateUpdated(function (Closure $set, $state) {
                            $set('slug', Str::slug($state));
                        }),
                    TextInput::make('slug')
                        ->disabled(),

                    RichEditor::make('description')
                        ->translateLabel()
                        ->required(),

                    Select::make('type')->options([
                        'fixed_value' => 'Fixed',
                        'percentage' => 'Percentage',
                    ])->reactive()
                        ->label(trans('Discount Type')),

                    TextInput::make('amount')
                        ->required()
                        ->numeric()
                        ->rules(['max:100'], fn (Closure $get) => $get('type') === 'percentage'),

                    Select::make('type')->options([
                        'order_sub_total' => 'Order Sub Total',
                        'delivery_fee' => 'Delivery Fee',
                    ])->label(trans('Discount Condition Type')),

                    TextInput::make('code')
                        ->suffixAction(
                            fn (?string $state): Action => Action::make('code')
                                ->icon('heroicon-o-cog')
                                ->action(fn (TextInput $component) => $component->state((new AutoGenerateCode())()))
                                ->tooltip(trans('auto generate code')),
                        ),
                    TextInput::make('max_uses')
                        ->required()
                        ->numeric()
                        ->label(trans('Maximum Usage per Discount Code')),

                    TextInput::make('max_uses_per_user')
                        ->required()
                        ->numeric()
                        ->label(trans('Maximum Usage per Customer')),
                ])->columns(2)
                    ->columnSpan(['lg' => 2]),

                Group::make([
                    Section::make(trans('Status & Period'))
                        ->schema([
                            Toggle::make('active'),
                            DateTimePicker::make('valid_start_date')
                                ->label('Start Date'),
                            DateTimePicker::make('valid_end_date')
                                ->label('Expiration Date'),
                        ]),
                ])
                    ->columnSpan(['lg' => 1]),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

            ])
            ->filters([

            ])
            ->filtersLayout(Layout::AboveContent)
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDiscounts::route('/'),
            'create' => CreateDiscount::route('/create'),
            'edit' => EditDiscount::route('/{record}/edit'),
        ];
    }
}
