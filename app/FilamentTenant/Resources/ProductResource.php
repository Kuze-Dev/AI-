<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Features\ECommerce\AllowGuestOrder;
use App\Features\ECommerce\ColorPallete;
use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources\ProductResource\Pages\EditProduct;
use App\FilamentTenant\Resources\ProductResource\RelationManagers\TiersRelationManager;
use App\FilamentTenant\Resources\ReviewResource\RelationManagers\ReviewRelationManager;
use App\FilamentTenant\Support\MetaDataForm;
use App\FilamentTenant\Support\ProductOption as ProductOptionSupport;
use App\FilamentTenant\Support\ProductVariant;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Closure;
use Domain\Product\Actions\DeleteProductAction;
use Domain\Product\Enums\Decision;
use Domain\Product\Enums\Status;
use Domain\Product\Enums\Taxonomy as EnumsTaxonomy;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductOption;
use Domain\Product\Rules\UniqueProductSkuRule;
use Domain\Taxonomy\Models\Taxonomy;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Support\Common\Rules\MinimumValueRule;
use Support\ConstraintsRelationships\Exceptions\DeleteRestrictedException;
use Support\Excel\Actions\ExportBulkAction;
use Throwable;

class ProductResource extends Resource
{
    use ContextualResource;

    protected static ?string $navigationGroup = 'eCommerce';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

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
                    Forms\Components\Card::make([
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

                    /** Form for variant section */
                    self::getVariantForm(),

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
                                ->hidden(fn (Closure $get) => ! $get('allow_stocks'))
                                ->required(fn (Closure $get) => $get('allow_stocks')),
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
                                ->rules([
                                    function () {
                                        return function (string $attribute, mixed $value, Closure $fail) {
                                            if ($value <= 0) {
                                                $attributeName = ucfirst(explode('.', $attribute)[1]);
                                                $fail("{$attributeName} must be above zero.");
                                            }
                                        };
                                    },
                                ])
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
                                ->rules([
                                    function () {
                                        return function (string $attribute, mixed $value, Closure $fail) {
                                            if ($value <= 0) {
                                                $attributeName = ucfirst(explode('.', $attribute)[1]);
                                                $fail("{$attributeName} must be above zero.");
                                            }
                                        };
                                    },
                                ])
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
                                        ->dehydrateStateUsing(fn (Closure $get) => Arr::flatten($get('taxonomies') ?? [], 1)),
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
                    ->default(
                        fn (Product $record) => $record->getFirstMedia('image') === null
                            ? 'https://via.placeholder.com/500x300/333333/fff?text=No+preview+available'
                            : null
                    )
                    ->extraImgAttributes(['class' => 'aspect-[5/3] object-fill']),
                Tables\Columns\TextColumn::make('name')
                    ->translateLabel()
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= $column->getLimit()) {
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
                Tables\Columns\BadgeColumn::make('status')
                    ->translateLabel()
                    ->formatStateUsing(fn ($state) => $state
                        ? ucfirst(STATUS::ACTIVE->value)
                        : ucfirst(STATUS::INACTIVE->value))
                    ->color(fn (Product $record) => $record->status ? 'success' : 'secondary')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(trans('Last Modified'))
                    ->dateTime(timezone: Auth::user()?->timezone)
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
                            } catch (DeleteRestrictedException $e) {
                                return false;
                            }
                        })
                        ->authorize('delete'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->translateLabel(),
                ExportBulkAction::make()
                    ->queue()
                    ->query(fn (Builder $query) => $query->with('productVariants')->latest())
                    ->mapUsing(
                        [
                            'product_id', 'is_variant', 'variant_id', 'name', 'variant_combination', 'sku',
                            'retail_price', 'selling_price', 'stock', 'status', 'is_digital_product',
                            'is_featured', 'is_special_offer', 'allow_customer_remarks', 'allow_stocks',
                            'allow_guest_purchase', 'weight', 'length', 'width', 'height', 'minimum_order_quantity',
                        ],
                        function (Product $product) {
                            $productData = [
                                [
                                    $product->id,
                                    Decision::NO->value,
                                    '',
                                    $product->name,
                                    '',
                                    $product->sku,
                                    $product->retail_price,
                                    $product->selling_price,
                                    $product->stock,
                                    $product->status ? Status::ACTIVE->value : STATUS::INACTIVE->value,
                                    $product->is_digital_product ? Decision::YES->value : Decision::NO->value,
                                    $product->is_featured ? Decision::YES->value : Decision::NO->value,
                                    $product->is_special_offer ? Decision::YES->value : Decision::NO->value,
                                    $product->allow_customer_remarks ? Decision::YES->value : Decision::NO->value,
                                    $product->allow_stocks ? Decision::YES->value : Decision::NO->value,
                                    $product->allow_guest_purchase ? Decision::YES->value : Decision::NO->value,
                                    $product->weight,
                                    $product->dimension['length'] ?? '',
                                    $product->dimension['width'] ?? '',
                                    $product->dimension['height'] ?? '',
                                    $product->minimum_order_quantity,
                                ],
                            ];
                            foreach ($product->productVariants as $variant) {
                                $productData[] =
                                    [
                                        $variant->product_id,
                                        Decision::YES->value,
                                        $variant->id,
                                        '',
                                        $variant->combination,
                                        $variant->sku,
                                        $variant->retail_price,
                                        $variant->selling_price,
                                        $variant->stock,
                                        $variant->status ? Status::ACTIVE->value : STATUS::INACTIVE->value,

                                    ];
                            }

                            return $productData;
                        }
                    ),

            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            ReviewRelationManager::class,
            ActivitiesRelationManager::class,
            TiersRelationManager::class,
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

    protected static function getVariantForm(): Forms\Components\Section
    {
        return Forms\Components\Section::make(trans('Variant'))->schema([
            /** For Manage Variant */
            ProductOptionSupport::make('product_options')
                ->translateLabel()
                ->itemLabel(fn (array $state) => $state['name'] ?? null)
                ->schema([
                    Forms\Components\Repeater::make('options')
                        ->translateLabel()
                        ->reactive()
                        ->itemLabel(fn (array $state): ?string => $state['name'])
                        ->afterStateHydrated(function (Forms\Components\Repeater $component, ?Product $record, ?array $state, EditProduct $livewire, Closure $get) {
                            if (! $record) {
                                return $state;
                            }

                            $productOptions = $livewire->data['product_options'];
                            if (($productOptions) !== null) {
                                $component->state($productOptions['options']);

                                return;
                            }

                            $record->productOptions->load('productOptionValues.media');
                            $mappedOptions = $record->productOptions->map(function (ProductOption $productOption) {
                                $mappedOptionValues = $productOption->productOptionValues->map(function ($optionValue) {
                                    $optionValueImages = $optionValue->media->map(fn ($medium) => $medium['uuid'])->toArray();

                                    return [
                                        'id' => $optionValue->id,
                                        'slug' => $optionValue->slug,
                                        'name' => $optionValue->name,
                                        'icon_type' => $optionValue->data['icon_type'] ?? 'colored',
                                        'icon_value' => $optionValue->data['icon_value'] ?? '',
                                        'images' => $optionValueImages,
                                        'product_option_id' => $optionValue->product_option_id,
                                    ];
                                })->toArray();

                                return [
                                    'id' => $productOption->id,
                                    'is_custom' => $productOption->is_custom,
                                    'name' => $productOption->name,
                                    'slug' => $productOption->slug,
                                    'productOptionValues' => $mappedOptionValues,
                                ];
                            });
                            $component->state($mappedOptions->toArray());
                        })
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->translateLabel()
                                ->maxLength(100)
                                ->required(),
                            Forms\Components\Toggle::make('is_custom')
                                ->label(
                                    fn ($state) => $state ? ucfirst(trans('Custom')) : ucfirst(trans('Regular'))
                                )
                                ->hidden(
                                    function (Closure $get) {
                                        if (! is_null($get('id'))) {
                                            return $get('../*')[0]['id'] !== $get('id');
                                        } else {
                                            return count($get('../*')) === 1 ? false : true;
                                        }
                                    }
                                )
                                ->extraAttributes(['class' => 'mt-2 mb-1'])
                                ->default(false)
                                ->helperText('Identify whether the option value in the form has customization.')
                                ->reactive(),
                            Forms\Components\Repeater::make('productOptionValues')
                                ->translateLabel()
                                ->columnSpan(2)
                                ->collapsible()
                                ->itemLabel(fn (array $state): ?string => $state['name'])
                                ->schema([
                                    Forms\Components\Group::make()
                                        ->schema(
                                            [
                                                Forms\Components\TextInput::make('name')
                                                    ->translateLabel()
                                                    ->maxLength(100)
                                                    ->lazy()
                                                    ->columnSpan(
                                                        fn (Closure $get) => $get('../../is_custom') ? 1 : 2
                                                    )
                                                    ->required(),
                                                Forms\Components\Select::make('icon_type')
                                                    ->default('text')
                                                    ->required()
                                                    ->options(fn () => tenancy()->tenant?->features()->active(ColorPallete::class) ? [
                                                        'text' => 'Text',
                                                        'color_palette' => 'Color Palette',
                                                    ] : [
                                                        'text' => 'Text',
                                                    ])
                                                    ->hidden(fn (Closure $get) => ! $get('../../is_custom'))
                                                    ->reactive(),
                                            ]
                                        )->columns(2),
                                    Forms\Components\ColorPicker::make('icon_value')
                                        ->label(trans('Icon Value (HEX)'))
                                        ->hidden(fn (Closure $get) => ! ($get('icon_type') === 'color_palette' && $get('../../is_custom'))),
                                    // Forms\Components\FileUpload::make('images')
                                    //     ->label(trans('Images (Preview Slides)'))
                                    //     ->image()
                                    //     ->mediaLibraryCollection('media')
                                    //     ->multiple()
                                    //     ->hidden(
                                    //         fn (Closure $get) => isset($get('../../../*')[1])
                                    //             && isset($get('../../../*')[1]['id'])
                                    //             && $get('../../../*')[1]['id'] === $get('../../id')
                                    //     )
                                    //     ->getUploadedFileUrlUsing(static function (Forms\Components\FileUpload $component, string $file): ?string {
                                    //         $mediaClass = config('media-library.media_model', Media::class);

                                    //         /** @var ?Media $media */
                                    //         $media = $mediaClass::findByUuid($file);

                                    //         if ($component->getVisibility() === 'private') {
                                    //             try {
                                    //                 return $media?->getTemporaryUrl(now()->addMinutes(5));
                                    //             } catch (Throwable $exception) {
                                    //                 // This driver does not support creating temporary URLs.
                                    //             }
                                    //         }

                                    //         return $media?->getUrl();
                                    //     }),

                                ])
                                ->minItems(1)
                                ->disableItemMovement(),
                        ])
                        ->disableItemMovement()
                        ->maxItems(2)
                        ->collapsible(),
                ]),
            ProductVariant::make('product_variants')
                ->translateLabel()
                ->itemLabel(fn (array $state) => $state['name'] ?? null)
                ->formatStateUsing(
                    function (?Product $record) {
                        if (! $record) {
                            return [];
                        }

                        $newArray = [];
                        foreach ($record->productVariants->toArray() as $key => $value) {
                            $newKey = 'record-'.$value['id'];
                            $newArray[$newKey] = $value;
                        }

                        return $newArray;
                    }
                )
                ->schema([
                    Forms\Components\Group::make()
                        ->schema([
                            Forms\Components\Group::make()
                                ->schema(function ($state) {
                                    $schemaArray = [];
                                    foreach ($state['combination'] as $key => $combination) {
                                        $schemaArray[$key] =
                                            Forms\Components\TextInput::make("combination[{$key}].option_value")
                                                ->formatStateUsing(fn () => ucfirst($combination['option_value']))
                                                ->label(trans(ucfirst($combination['option'])))
                                                ->disabled();
                                    }

                                    return $schemaArray;
                                })->columns(2),
                            Forms\Components\Section::make('Inventory')
                                ->translateLabel()
                                ->schema([
                                    Forms\Components\TextInput::make('sku')
                                        ->maxLength(100)
                                        // ->rule(function(EditProduct $livewire) {
                                        //     dump(func_get_args());
                                        // })
                                        ->rule(fn (EditProduct $livewire) => new UniqueProductSkuRule($livewire))
                                        ->required(),
                                    Forms\Components\TextInput::make('stock')
                                        ->translateLabel()
                                        ->numeric()
                                        ->minValue(0)
                                        ->dehydrateStateUsing(fn ($state) => (int) $state),
                                ])->columns(2),
                            Forms\Components\Section::make('Pricing')
                                ->translateLabel()
                                ->schema([
                                    Forms\Components\TextInput::make('retail_price')
                                        ->translateLabel()
                                        // Put custom rule to validate minimum value
                                        ->mask(fn (Forms\Components\TextInput\Mask $mask) => $mask->money(
                                            prefix: '$',
                                            thousandsSeparator: ',',
                                            decimalPlaces: 2,
                                            isSigned: false
                                        ))
                                        ->rules([
                                            function () {
                                                return function (string $attribute, mixed $value, Closure $fail) {
                                                    if ($value <= 0) {
                                                        $fail("{$attribute} must be above zero.");
                                                    }
                                                };
                                            },
                                        ])
                                        ->dehydrateStateUsing(fn ($state) => (float) $state)
                                        ->required(),

                                    Forms\Components\TextInput::make('selling_price')
                                        ->translateLabel()
                                        // Put custom rule to validate minimum value
                                        ->mask(fn (Forms\Components\TextInput\Mask $mask) => $mask->money(
                                            prefix: '$',
                                            thousandsSeparator: ',',
                                            decimalPlaces: 2,
                                            isSigned: false
                                        ))
                                        ->rules([
                                            function () {
                                                return function (string $attribute, mixed $value, Closure $fail) {
                                                    if ($value <= 0) {
                                                        $attributeName = ucfirst(explode('.', $attribute)[1]);
                                                        $fail("{$attributeName} must be above zero.");
                                                    }
                                                };
                                            },
                                        ])
                                        ->dehydrateStateUsing(fn ($state) => (float) $state)
                                        ->required(),
                                ])->columns(2),
                            // Forms\Components\Section::make('Status')
                            //     ->translateLabel()
                            //     ->schema([
                            //         Forms\Components\Toggle::make('status')
                            //             ->label(
                            //                 fn ($state) => $state ? trans(STATUS::ACTIVE->value) : trans(STATUS::INACTIVE->value)
                            //             )
                            //             ->helperText('This product variant will be hidden from all sales channels.'),
                            //     ]),
                        ]),
                ]),
        ])->hiddenOn('create');
    }
}
