<?php

namespace App\FilamentTenant\Resources;

use App\FilamentTenant\Support\MetaDataForm;
use App\FilamentTenant\Support\RouteUrlFieldset;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Domain\Product\Models\Product;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\Facades\Auth;
use Filament\Forms;
use Filament\Forms\Components\Component;

class ProductResource extends Resource
{
    use ContextualResource;

    protected static ?string $navigationGroup = 'eCommerce';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([
                Forms\Components\Group::make()->schema([
                    Forms\Components\Card::make([
                        Forms\Components\TextInput::make('name')
                            ->label('Product Name')
                            ->unique(ignoreRecord: true)
                            ->required(),
                        Forms\Components\Textarea::make('description'),
                        Forms\Components\FileUpload::make('image')
                            ->label('Media')
                            ->mediaLibraryCollection('image')
                            ->image(),
                        Forms\Components\Section::make('Customer Remarks')
                            ->schema([
                                Forms\Components\Toggle::make('allow_customer_remarks')
                                    ->label('Allow customer to add remarks upon purchase'),
                                Forms\Components\Checkbox::make('allow_remark_with_image')
                                    ->label('Allow to add media'),
                            ]),
                    ]),
                    Forms\Components\Section::make('Section Display')
                        ->schema([
                            Forms\Components\Toggle::make('is_special_offer')
                                ->required(),
                            Forms\Components\Toggle::make('is_featured')
                                ->required(),
                        ])->columns(2),
                    Forms\Components\Section::make('Shipping')
                        ->schema([
                            Forms\Components\TextInput::make('shipping_fee')
                                ->dehydrateStateUsing(fn ($state) => (float) $state)
                                ->helperText('Leave this field blank if there is no shipping fee.'),
                        ]),
                    Forms\Components\Section::make('Inventory')
                        ->schema([
                            Forms\Components\TextInput::make('sku')
                                ->unique(ignoreRecord: true)
                                ->required(),
                            Forms\Components\TextInput::make('stock')
                                ->dehydrateStateUsing(fn ($state) => (int) $state)
                                ->required(),

                        ])->columns(2),
                    Forms\Components\Section::make('Pricing')
                        ->schema([
                            Forms\Components\TextInput::make('retail_price')
                                ->dehydrateStateUsing(fn ($state) => (float) $state)
                                ->required(),
                            Forms\Components\TextInput::make('selling_price')
                                ->dehydrateStateUsing(fn ($state) => (float) $state)
                                ->required(),

                        ])->columns(2),
                ])->columnSpan(2),
                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make('Status')
                        ->schema([
                            Forms\Components\Toggle::make('status')
                                ->helperText('This product will be hidden from all sales channels.')
                                ->required(),
                        ]),
                    MetaDataForm::make('Meta Data')
                ])->columnSpan(1)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('selling_price')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->formatStateUsing(fn ($state) => $state ? 'Active' : 'Inactive'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->authorize('update'),
                Tables\Actions\DeleteAction::make()
                    ->authorize('delete'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ProductResource\Pages\ListProducts::route('/'),
            'create' => ProductResource\Pages\CreateProduct::route('/create'),
            'edit' => ProductResource\Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
