<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Features\ECommerce\AllowGuestOrder;
use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources\ProductResource\RelationManagers\OptionsRelationManager;
use App\FilamentTenant\Resources\ProductResource\RelationManagers\TiersRelationManager;
use App\FilamentTenant\Resources\ProductResource\RelationManagers\VariantsRelationManager;
use App\FilamentTenant\Resources\ReviewResource\RelationManagers\ReviewRelationManager;
use App\FilamentTenant\Support\MetaDataForm;
use Closure;
use Domain\Product\Actions\DeleteProductAction;
use Domain\Product\Enums\Decision;
use Domain\Product\Enums\Status;
use Domain\Product\Enums\Taxonomy as EnumsTaxonomy;
use Domain\Product\Models\Product;
use Domain\Taxonomy\Models\Taxonomy;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Table;
use HalcyonAgile\FilamentExport\Actions\ExportBulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Support\Common\Rules\MinimumValueRule;
use Support\ConstraintsRelationships\Exceptions\DeleteRestrictedException;

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
        $taxonomies = Taxonomy::with('taxonomyTerms')
            ->whereIn(
                'slug',
                [EnumsTaxonomy::BRAND->value, EnumsTaxonomy::CATEGORIES->value]
            )->get();

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
                            Forms\Components\FileUpload::make('images')
                                ->translateLabel()
                                ->mediaLibraryCollection('image')
                                ->image()
                                ->multiple()
                                ->required(),
                            Forms\Components\FileUpload::make('videos')
                                ->translateLabel()
                                ->mediaLibraryCollection('video')
                                ->acceptedFileTypes([
                                    'video/*',
                                ])
                                ->maxSize(25000),
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
                                ->rules([
                                    new MinimumValueRule(0.01),
                                ])
                                ->dehydrateStateUsing(fn ($state) => (float) $state),

                            Forms\Components\Fieldset::make('dimension')
                                ->label(trans('Dimension (cm)'))
                                ->schema([
                                    Forms\Components\TextInput::make('length')
                                        ->translateLabel()
                                        ->numeric()
                                        ->required()
                                        ->rules([
                                            new MinimumValueRule(0.01),
                                        ])
                                        ->afterStateHydrated(fn (Forms\Components\TextInput $component, ?Product $record, ?array $state) => ! $record ? $state : $component->state($record->dimension['length'] ?? 0))
                                        ->dehydrateStateUsing(fn ($state) => (float) $state),

                                    Forms\Components\TextInput::make('width')
                                        ->translateLabel()
                                        ->numeric()
                                        ->required()
                                        ->rules([
                                            new MinimumValueRule(0.01),
                                        ])
                                        ->afterStateHydrated(fn (Forms\Components\TextInput $component, ?Product $record, ?array $state) => ! $record ? $state : $component->state($record->dimension['width'] ?? 0))
                                        ->dehydrateStateUsing(fn ($state) => (float) $state),

                                    Forms\Components\TextInput::make('height')
                                        ->translateLabel()
                                        ->numeric()
                                        ->required()
                                        ->rules([
                                            new MinimumValueRule(0.01),
                                        ])
                                        ->afterStateHydrated(fn (Forms\Components\TextInput $component, ?Product $record, ?array $state) => ! $record ? $state : $component->state($record->dimension['height'] ?? 0))
                                        ->dehydrateStateUsing(fn ($state) => (float) $state),
                                ])->columns(3),
                        ]),
                    Forms\Components\Section::make('Inventory')
                        ->translateLabel()
                        ->schema([
                            Forms\Components\TextInput::make('minimum_order_quantity')
                                ->translateLabel()
                                ->numeric()
                                ->integer()
                                ->required()
                                ->default(1)
                                ->dehydrateStateUsing(fn ($state) => (int) $state),
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
                                ->dehydrateStateUsing(fn ($state) => (int) $state)
                                ->hidden(fn (Get $get) => ! $get('allow_stocks'))
                                ->required(fn (Get $get) => $get('allow_stocks')),
                        ])->columns(2),
                    Forms\Components\Section::make('Pricing')
                        ->translateLabel()
                        ->schema([
                            Forms\Components\TextInput::make('retail_price')
                                ->translateLabel()
                                ->mask(fn (Forms\Components\TextInput\Mask $mask) => $mask->money(
                                    prefix: '$',
                                    thousandsSeparator: ',',
                                    decimalPlaces: 2,
                                    isSigned: false
                                ))
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
                                ->mask(fn (Forms\Components\TextInput\Mask $mask) => $mask->money(
                                    prefix: '$',
                                    thousandsSeparator: ',',
                                    decimalPlaces: 2,
                                    isSigned: false
                                ))
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
                ])->columnSpan(2),
                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make('Status')
                        ->translateLabel()
                        ->schema([
                            Forms\Components\Toggle::make('status')
                                ->label(
                                    fn ($state) => $state ? ucfirst(trans(Status::ACTIVE->value)) : ucfirst(trans(Status::INACTIVE->value))
                                )
                                ->reactive()
                                ->helperText('This product will be hidden from all sales channels.'),
                            Forms\Components\Toggle::make('allow_guest_purchase')
                                ->helperText('Item can be purchased by guests.')
                                ->default(false)
                                ->columnSpan(2)
                                ->hidden(fn () => ! tenancy()->tenant?->features()->active(AllowGuestOrder::class) ? true : false),
                        ]),
                    Forms\Components\Section::make('Associations')
                        ->translateLabel()
                        ->schema([
                            Forms\Components\Group::make()
                                ->schema([
                                    Forms\Components\Group::make()
                                        ->statePath('taxonomies')
                                        ->schema(
                                            fn () => $taxonomies->map(
                                                fn (Taxonomy $taxonomy) => Forms\Components\Select::make($taxonomy->name)
                                                    ->statePath((string) $taxonomy->id)
                                                    ->multiple(
                                                        fn () => $taxonomy->slug === EnumsTaxonomy::BRAND->value ? false : true
                                                    )
                                                    ->options(
                                                        $taxonomy->taxonomyTerms->sortBy('name')
                                                            ->mapWithKeys(fn (TaxonomyTerm $term) => [$term->id => $term->name])
                                                            ->toArray()
                                                    )
                                                    ->formatStateUsing(
                                                        fn (?Product $record) => $record?->taxonomyTerms->where('taxonomy_id', $taxonomy->id)
                                                            ->pluck('id')
                                                            ->toArray() ?? []
                                                    )
                                                    ->required()
                                            )->toArray()
                                        )
                                        ->dehydrated(false),
                                    Forms\Components\Hidden::make('taxonomy_terms')
                                        ->dehydrateStateUsing(fn (Get $get) => Arr::flatten($get('taxonomies') ?? [], 1)),
                                ])
                                ->when(fn () => ! empty($taxonomies->toArray())),
                        ]),
                    MetaDataForm::make('Meta Data'),
                ])->columnSpan(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')
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
                    ->formatStateUsing(fn ($state) => $state
                        ? ucfirst(STATUS::ACTIVE->value)
                        : ucfirst(STATUS::INACTIVE->value))
                    ->color(fn (Product $record) => $record->status ? 'success' : 'secondary')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(trans('Last Modified'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->translateLabel()
                    ->options(['1' => STATUS::ACTIVE->value, '0' => STATUS::INACTIVE->value])
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
                    ->translateLabel()
                    ->authorize('update'),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DeleteAction::make()
                        ->translateLabel()
                        ->using(function (Product $record) {
                            try {
                                return app(DeleteProductAction::class)->execute($record);
                            } catch (DeleteRestrictedException) {
                                return false;
                            }
                        })
                        ->authorize('delete'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->translateLabel(),

                // TODO: export
                //                ExportBulkAction::make()
                //                    ->queue()
                //                    ->query(fn (Builder $query) => $query->with('productVariants')->latest())
                //                    ->mapUsing(
                //                        [
                //                            'product_id', 'is_variant', 'variant_id', 'name', 'variant_combination', 'sku',
                //                            'retail_price', 'selling_price', 'stock', 'status', 'is_digital_product',
                //                            'is_featured', 'is_special_offer', 'allow_customer_remarks', 'allow_stocks',
                //                            'allow_guest_purchase', 'weight', 'length', 'width', 'height', 'minimum_order_quantity',
                //                        ],
                //                        function (Product $product) {
                //                            $productData = [
                //                                [
                //                                    $product->id,
                //                                    Decision::NO->value,
                //                                    '',
                //                                    $product->name,
                //                                    '',
                //                                    $product->sku,
                //                                    $product->retail_price,
                //                                    $product->selling_price,
                //                                    $product->stock,
                //                                    $product->status ? Status::ACTIVE->value : STATUS::INACTIVE->value,
                //                                    $product->is_digital_product ? Decision::YES->value : Decision::NO->value,
                //                                    $product->is_featured ? Decision::YES->value : Decision::NO->value,
                //                                    $product->is_special_offer ? Decision::YES->value : Decision::NO->value,
                //                                    $product->allow_customer_remarks ? Decision::YES->value : Decision::NO->value,
                //                                    $product->allow_stocks ? Decision::YES->value : Decision::NO->value,
                //                                    $product->allow_guest_purchase ? Decision::YES->value : Decision::NO->value,
                //                                    $product->weight,
                //                                    $product->dimension['length'] ?? '',
                //                                    $product->dimension['width'] ?? '',
                //                                    $product->dimension['height'] ?? '',
                //                                    $product->minimum_order_quantity,
                //                                ],
                //                            ];
                //                            foreach ($product->productVariants as $variant) {
                //                                $productData[] =
                //                                    [
                //                                        $variant->product_id,
                //                                        Decision::YES->value,
                //                                        $variant->id,
                //                                        '',
                //                                        $variant->combination,
                //                                        $variant->sku,
                //                                        $variant->retail_price,
                //                                        $variant->selling_price,
                //                                        $variant->stock,
                //                                        $variant->status ? Status::ACTIVE->value : STATUS::INACTIVE->value,
                //
                //                                    ];
                //                            }
                //
                //                            return $productData;
                //                        }
                //                    ),

            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            VariantsRelationManager::class,
            OptionsRelationManager::class,
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
