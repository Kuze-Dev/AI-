<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\FilamentTenant\Resources\ReviewResource\RelationManagers\ReviewRelationManager;
use App\FilamentTenant\Support\MetaDataForm;
use App\FilamentTenant\Support\ProductOption as ProductOptionSupport;
use App\FilamentTenant\Support\ProductVariant;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Domain\Product\Models\Product;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\Facades\Auth;
use Filament\Forms;
use Closure;
use Domain\Product\Models\ProductOption;
use Domain\Taxonomy\Models\Taxonomy;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Filters\Layout;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Builder;
use App\FilamentTenant\Support\Contracts\HasProductOptions;

class ProductResource extends Resource
{
    use ContextualResource;

    protected static ?string $navigationGroup = 'eCommerce';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function form(Form $form): Form
    {
        $taxonomies = Taxonomy::with('taxonomyTerms')->whereIn('slug', ['brand', 'categories'])->get();

        return $form
            ->columns(3)
            ->schema([
                Forms\Components\Group::make()->schema([
                    Forms\Components\Card::make([
                        Forms\Components\TextInput::make('name')
                            ->label('Product Name')
                            ->unique(ignoreRecord: true)
                            ->required(),
                        Forms\Components\RichEditor::make('description'),
                        Forms\Components\FileUpload::make('images')
                            ->label('Media')
                            ->mediaLibraryCollection('image')
                            ->image()
                            ->multiple()
                            ->required(),
                    ]),
                    Forms\Components\Section::make('Customer Remarks')
                        ->schema([
                            Forms\Components\Toggle::make('allow_customer_remarks')
                                ->label('Allow customer to add remarks upon purchase'),
                        ]),
                    Forms\Components\Section::make('Section Display')
                        ->schema([
                            Forms\Components\Toggle::make('is_special_offer'),
                            Forms\Components\Toggle::make('is_featured'),
                        ])->columns(2),
                    Forms\Components\Section::make('Shipping')
                        ->schema([
                            Forms\Components\TextInput::make('weight')
                                ->numeric()
                                ->dehydrateStateUsing(fn ($state) => (float) $state),

                            Forms\Components\Fieldset::make('dimension')
                                ->label('Dimension')
                                ->schema([
                                    Forms\Components\TextInput::make('length')
                                        ->numeric()
                                        ->afterStateHydrated(fn (Forms\Components\TextInput $component, ?Product $record, ?array $state) => ! $record ? $state : $component->state($record->dimension['length']))
                                        ->dehydrateStateUsing(fn ($state) => (float) $state)
                                        ->label('Length'),

                                    Forms\Components\TextInput::make('width')
                                        ->numeric()
                                        ->afterStateHydrated(fn (Forms\Components\TextInput $component, ?Product $record, ?array $state) => ! $record ? $state : $component->state($record->dimension['width']))
                                        ->dehydrateStateUsing(fn ($state) => (float) $state)
                                        ->label('Width'),

                                    Forms\Components\TextInput::make('height')
                                        ->numeric()
                                        ->afterStateHydrated(fn (Forms\Components\TextInput $component, ?Product $record, ?array $state) => ! $record ? $state : $component->state($record->dimension['height']))
                                        ->dehydrateStateUsing(fn ($state) => (float) $state)
                                        ->label('Height'),
                                ])->columns(3),
                        ]),
                    /** Form for variant section */
                    self::getVariantForm(),
                    Forms\Components\Section::make('Inventory')
                        ->schema([
                            Forms\Components\TextInput::make('minimum_order_quantity')
                                ->numeric()
                                ->integer()
                                ->required()
                                ->default(1)
                                ->dehydrateStateUsing(fn ($state) => (int) $state),
                            Forms\Components\TextInput::make('sku')
                                ->unique(ignoreRecord: true)
                                ->required(),
                            Forms\Components\TextInput::make('stock')
                                ->numeric()
                                ->dehydrateStateUsing(fn ($state) => (int) $state)
                                ->required(),
                        ])->columns(2),
                    Forms\Components\Section::make('Pricing')
                        ->schema([
                            Forms\Components\TextInput::make('retail_price')
                                ->mask(fn (Forms\Components\TextInput\Mask $mask) => $mask->money(
                                    prefix: '$',
                                    thousandsSeparator: ',',
                                    decimalPlaces: 2,
                                    isSigned: false
                                ))
                                ->dehydrateStateUsing(fn ($state) => (float) $state)
                                ->required(),

                            Forms\Components\TextInput::make('selling_price')
                                ->mask(fn (Forms\Components\TextInput\Mask $mask) => $mask->money(
                                    prefix: '$',
                                    thousandsSeparator: ',',
                                    decimalPlaces: 2,
                                    isSigned: false
                                ))
                                ->dehydrateStateUsing(fn ($state) => (float) $state)
                                ->required(),
                        ])->columns(2),
                ])->columnSpan(2),
                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make('Status')
                        ->schema([
                            Forms\Components\Toggle::make('status')
                                ->label(
                                    fn ($state) => $state ? 'Active' : 'Inactive'
                                )
                                ->helperText('This product will be hidden from all sales channels.'),
                        ]),
                    Forms\Components\Section::make('Associations')
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
                                                        fn () => $taxonomy->slug === 'brand' ? false : true
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
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('selling_price')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->formatStateUsing(fn ($state) => $state ? 'Active' : 'Inactive')
                    ->color(fn (Product $record) => $record->status ? 'success' : 'secondary')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Modified')
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['1' => 'Active', '0' => 'Inactive'])
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
            ->filtersLayout(Layout::AboveContent)
            ->actions([
                Tables\Actions\EditAction::make()
                    ->authorize('update'),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            ReviewRelationManager::class,
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
                ->itemLabel(fn (array $state) => $state['name'] ?? null)
                ->schema([
                    Forms\Components\Repeater::make('options')
                        ->afterStateHydrated(function (Forms\Components\Repeater $component, ?Product $record, ?array $state, HasProductOptions $livewire) {
                            if ( ! $record) {
                                return $state;
                            }

                            if (($livewire->data['product_options']) !== null) {
                                $component->state($livewire->data['product_options']['options']);

                                return;
                            }
                            $record->productOptions->load('productOptionValues');
                            $mappedOptions = $record->productOptions->map(function (ProductOption $productOption) {
                                return [
                                    'id' => $productOption->id,
                                    'name' => $productOption->name,
                                    'slug' => $productOption->slug,
                                    'productOptionValues' => $productOption->productOptionValues,
                                ];
                            });
                            $component->state($mappedOptions->toArray());
                        })
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->required(),
                            Forms\Components\Repeater::make('productOptionValues')
                                ->schema([
                                    Forms\Components\TextInput::make('name')
                                        ->label('')
                                        ->lazy()
                                        ->required(),
                                ])
                                ->minItems(1)
                                ->disableItemMovement(),
                        ])
                        ->disableItemMovement()
                        ->minItems(1)
                        ->maxItems(2)
                        ->collapsible(),
                ]),
            ProductVariant::make('product_variants')
                ->itemLabel(fn (array $state) => $state['name'] ?? null)
                ->formatStateUsing(
                    function (?Product $record) {
                        if ( ! $record) {
                            return [];
                        }

                        $newArray = [];
                        foreach ($record->productVariants->toArray() as $key => $value) {
                            $newKey = 'record-' . $value['id'];
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
                                                ->label(ucfirst($combination['option']))
                                                ->disabled();
                                    }

                                    return $schemaArray;
                                })->columns(2),
                            Forms\Components\Section::make('Inventory')
                                ->schema([
                                    Forms\Components\TextInput::make('sku')
                                        ->unique(ignoreRecord: true)
                                        ->required(),
                                    Forms\Components\TextInput::make('stock')
                                        ->numeric()
                                        ->dehydrateStateUsing(fn ($state) => (int) $state)
                                        ->required(),
                                ])->columns(2),
                            Forms\Components\Section::make('Pricing')
                                ->schema([
                                    Forms\Components\TextInput::make('retail_price')
                                        ->mask(fn (Forms\Components\TextInput\Mask $mask) => $mask->money(
                                            prefix: '$',
                                            thousandsSeparator: ',',
                                            decimalPlaces: 2,
                                            isSigned: false
                                        ))
                                        ->dehydrateStateUsing(fn ($state) => (float) $state)
                                        ->required(),

                                    Forms\Components\TextInput::make('selling_price')
                                        ->mask(fn (Forms\Components\TextInput\Mask $mask) => $mask->money(
                                            prefix: '$',
                                            thousandsSeparator: ',',
                                            decimalPlaces: 2,
                                            isSigned: false
                                        ))
                                        ->dehydrateStateUsing(fn ($state) => (float) $state)
                                        ->required(),
                                ])->columns(2),
                            Forms\Components\Section::make('Status')
                                ->schema([
                                    Forms\Components\Toggle::make('status')
                                        ->label(
                                            fn ($state) => $state ? 'Active' : 'Inactive'
                                        )
                                        ->helperText('This product variant will be hidden from all sales channels.'),
                                ]),
                        ]),
                ]),
        ])->hiddenOn('create');
    }
}
