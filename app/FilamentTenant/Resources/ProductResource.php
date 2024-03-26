<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Features\ECommerce\AllowGuestOrder;
use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources\ProductResource\RelationManagers\ProductOptionsRelationManager;
use App\FilamentTenant\Resources\ProductResource\RelationManagers\ProductOptionValuesRelationManager;
use App\FilamentTenant\Resources\ProductResource\RelationManagers\ProductVariantsRelationManager;
use App\FilamentTenant\Resources\ProductResource\RelationManagers\TiersRelationManager;
use App\FilamentTenant\Resources\ReviewResource\RelationManagers\ReviewRelationManager;
use App\FilamentTenant\Support\MetaDataFormV2;
use Closure;
use Domain\Product\Enums\Status;
use Domain\Product\Enums\Taxonomy as EnumsTaxonomy;
use Domain\Product\Exports\ProductExporter;
use Domain\Product\Models\Product;
use Domain\Taxonomy\Models\Taxonomy;
use Domain\Tenant\TenantFeatureSupport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function getNavigationGroup(): ?string
    {
        return trans('eCommerce');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([
                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make([
                        Forms\Components\TextInput::make('name')
                            ->label(trans('Product Name'))
                            ->unique(ignoreRecord: true)
                            ->maxLength(100)
                            ->required(),
                        Forms\Components\RichEditor::make('description')
                            ->translateLabel()
                            ->maxLength(255),
                    ]),
                    Forms\Components\Section::make('Media')
                        ->translateLabel()
                        ->schema([
                            Forms\Components\SpatieMediaLibraryFileUpload::make('image')
                                ->translateLabel()
                                ->collection('image')
                                ->image()
                                ->multiple()
                                ->required(),
                            Forms\Components\SpatieMediaLibraryFileUpload::make('video')
                                ->translateLabel()
                                ->collection('video')
                                ->acceptedFileTypes([
                                    'video/*',
                                ])
                                ->required()
                                ->maxSize(25_000),
                        ]),

                    Forms\Components\Section::make('Customer Remarks')
                        ->translateLabel()
                        ->schema([
                            Forms\Components\Toggle::make('allow_customer_remarks')
                                ->label(trans('Allow customer to add remarks upon purchase')),
                        ]),
                    Forms\Components\Section::make('Section Display')
                        ->translateLabel()
                        ->schema([
                            Forms\Components\Toggle::make('is_special_offer')
                                ->translateLabel(),
                            Forms\Components\Toggle::make('is_featured')
                                ->translateLabel(),
                        ])->columns(2),
                    Forms\Components\Section::make('Shipping Size')
                        ->translateLabel()
                        ->schema([
                            Forms\Components\TextInput::make('weight')
                                ->label(trans('Weight (lbs)'))
                                ->numeric()
                                ->required()
                                ->minValue(0.01),

                            Forms\Components\KeyValue::make('dimension')
                                ->label(trans('Dimension (cm)'))
                                ->addable(false)
                                ->deletable(false)
                                ->editableKeys(false)
                                ->keyLabel(trans('Fields'))
                                ->required()
                                ->schema([
                                    Forms\Components\TextInput::make('length')
                                        ->translateLabel()
                                        ->numeric()
                                        ->required()
                                        ->minValue(0.01),

                                    Forms\Components\TextInput::make('width')
                                        ->translateLabel()
                                        ->numeric()
                                        ->required()
                                        ->minValue(0.01),

                                    Forms\Components\TextInput::make('height')
                                        ->translateLabel()
                                        ->numeric()
                                        ->required()
                                        ->minValue(0.01),
                                ])
                                ->columns(3),
                        ]),
                    Forms\Components\Section::make('Inventory')
                        ->translateLabel()
                        ->schema([
                            Forms\Components\TextInput::make('minimum_order_quantity')
                                ->translateLabel()
                                ->numeric()
                                ->integer()
                                ->required()
                                ->default(1),
                            Forms\Components\TextInput::make('sku')
                                ->unique(ignoreRecord: true)
                                ->maxLength(100)
                                ->required(),
                            Forms\Components\Toggle::make('allow_stocks')
                                ->label(trans('Allow stock control'))
                                ->default(true)
                                ->columnSpan(2)
                                ->reactive(),
                            Forms\Components\TextInput::make('stock')
                                ->translateLabel()
                                ->numeric()
                                ->minValue(0)
                                ->hidden(fn (Get $get) => ! $get('allow_stocks'))
                                ->required(fn (Get $get) => $get('allow_stocks')),
                        ])
                        ->columns(2),
                    Forms\Components\Section::make('Pricing')
                        ->translateLabel()
                        ->schema([
                            Forms\Components\TextInput::make('retail_price')
                                ->translateLabel()
                                ->numeric()
                                ->prefix('$')
                                ->rule(
                                    fn () => function (string $attribute, mixed $value, Closure $fail) {
                                        if ($value <= 0) {
                                            $attributeName = ucfirst(explode('.', $attribute)[1]);
                                            $fail("{$attributeName} must be above zero.");
                                        }
                                    },
                                )
                                ->dehydrateStateUsing(fn ($state) => (float) $state)
                                ->required(),

                            Forms\Components\TextInput::make('selling_price')
                                ->translateLabel()
                                ->numeric()
                                ->prefix('$')
                                ->rule(
                                    fn () => function (string $attribute, mixed $value, Closure $fail) {
                                        if ($value <= 0) {
                                            $attributeName = ucfirst(explode('.', $attribute)[1]);
                                            $fail("{$attributeName} must be above zero.");
                                        }
                                    },
                                )
                                ->dehydrateStateUsing(fn ($state) => (float) $state)
                                ->required(),
                        ])->columns(2),
                ])
                    ->columnSpan(2),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Status')
                            ->translateLabel()
                            ->schema([
                                Forms\Components\Toggle::make('status')
                                    ->label(
                                        fn (bool $state) => $state
                                            ? Status::ACTIVE->getLabel()
                                            : Status::INACTIVE->getLabel()
                                    )
                                    ->reactive()
                                    ->helperText('This product will be hidden from all sales channels.'),
                                Forms\Components\Toggle::make('allow_guest_purchase')
                                    ->helperText('Item can be purchased by guests.')
                                    ->default(false)
                                    ->columnSpan(2)
                                    ->visible(fn () => TenantFeatureSupport::active(AllowGuestOrder::class)),
                            ]),
                        Forms\Components\Section::make('Associations')
                            ->translateLabel()
                            ->schema([
                                Forms\Components\Select::make('taxonomyTermsBranch')
                                    ->label(Taxonomy::whereSlug(EnumsTaxonomy::BRAND->value)->value('name'))
                                    ->relationship(titleAttribute: 'name')
                                    ->required()
                                    // Add fillable property [taxonomyTermsBranch] to allow mass assignment on [Domain\Product\Models\Product].
                                    ->multiple()
                                    ->maxItems(1)
                                    ->preload()
                                    ->searchable()
                                    ->visible(
                                        fn () => Taxonomy::where(
                                            'slug',
                                            EnumsTaxonomy::BRAND->value
                                        )
                                            ->exists()
                                    ),
                                Forms\Components\Select::make('taxonomyTermsCategory')
                                    ->label(EnumsTaxonomy::CATEGORIES->getLabel())
                                    ->relationship(titleAttribute: 'name')
                                    ->required()
                                    ->multiple()
                                    ->preload()
                                    ->searchable()
                                    ->visible(
                                        fn () => Taxonomy::where(
                                            'slug',
                                            EnumsTaxonomy::CATEGORIES->value
                                        )
                                            ->exists()
                                    ),
                            ]),
                        MetaDataFormV2::make(),
                    ])
                    ->columnSpan(1),
            ]);
    }

    /**
     * @throws \Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')
                    ->translateLabel()
                    ->collection('image')
                    ->extraImgAttributes(['class' => 'aspect-[5/3] object-fill']),
                Tables\Columns\TextColumn::make('name')
                    ->translateLabel()
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        // Only render the tooltip if the column content exceeds the length limit.
                        return $state;
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('selling_price')
                    ->translateLabel()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->translateLabel()
                    ->badge()
                    ->formatStateUsing(
                        fn (bool $state) => $state
                            ? STATUS::ACTIVE->getLabel()
                            : STATUS::INACTIVE->getLabel()
                    )
                    ->color(
                        fn (bool $state) => $state
                            ? STATUS::ACTIVE->getColor()
                            : STATUS::INACTIVE->getColor()
                    )
                    ->icon(
                        fn (bool $state) => $state
                            ? STATUS::ACTIVE->getIcon()
                            : STATUS::INACTIVE->getIcon()
                    )
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(trans('Last Modified'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->translateLabel()
                    ->options(['1' => STATUS::ACTIVE->getLabel(), '0' => STATUS::INACTIVE->getLabel()])
                    ->query(function (Builder $query, array $data) {
                        $query->when(filled($data['value']), function (Builder $query) use ($data) {
                            $query->when(filled($data['value']), function (Builder $query) use ($data) {
                                /** @var Product|Builder $query */
                                match ($data['value']) {
                                    '1' => $query->where('status', true),
                                    '0' => $query->where('status', false),
                                    default => '',
                                };
                            });
                        });
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->translateLabel(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DeleteAction::make()
                        ->translateLabel(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->translateLabel(),

                Tables\Actions\ExportBulkAction::make()
                    ->exporter(ProductExporter::class)
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary'),

            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            ProductVariantsRelationManager::class,
            ProductOptionsRelationManager::class,
            ProductOptionValuesRelationManager::class,
            TiersRelationManager::class,
            ReviewRelationManager::class,
            ActivitiesRelationManager::class,
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
