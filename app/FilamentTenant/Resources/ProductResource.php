<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\FilamentTenant\Support\MetaDataForm;
use App\FilamentTenant\Support\Tree;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Domain\Product\Models\Product;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\Facades\Auth;
use Filament\Forms;
use Closure;
use Domain\Taxonomy\Models\Taxonomy;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Filament\Tables\Filters\Layout;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    use ContextualResource;

    protected static ?string $navigationGroup = 'eCommerce';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        $taxonomies = Taxonomy::with('taxonomyTerms')->whereIn('slug', ['brand', 'category'])->get();

        return $form
            ->columns(3)
            ->schema([
                Forms\Components\Group::make()->schema([
                    Forms\Components\Card::make([
                        Forms\Components\TextInput::make('name')
                            ->label('Product Name')
                            ->unique(ignoreRecord: true)
                            ->required(),
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
                        Forms\Components\RichEditor::make('description')->required(),
                        Forms\Components\FileUpload::make('image')
                            ->label('Media')
                            ->mediaLibraryCollection('image')
                            ->image()
                            ->required(),
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
                            Forms\Components\Toggle::make('is_special_offer'),
                            Forms\Components\Toggle::make('is_featured'),
                        ])->columns(2),
                    Forms\Components\Section::make('Shipping')
                        ->schema([
                            Forms\Components\TextInput::make('shipping_fee')
                                ->mask(fn (Forms\Components\TextInput\Mask $mask) => $mask->money(
                                    prefix: '$',
                                    thousandsSeparator: ',',
                                    decimalPlaces: 2,
                                    isSigned: false
                                ))
                                ->dehydrateStateUsing(fn ($state) => (float) $state)
                                ->helperText('Leave this field blank if there is no shipping fee.'),
                        ]),
                    // Reference: Block & Taxonomy
                    Forms\Components\Section::make(trans('Variants (work in progress)'))->schema([
                        Tree::make('variants')
                            ->formatStateUsing(
                                fn () => []
                            )
                            ->itemLabel(fn (array $state) => $state['name'] ?? null)
                            ->schema([
                                Forms\Components\Grid::make(['md' => 1])
                                    ->schema([
                                        Forms\Components\TextInput::make('variant_name')
                                            ->required()
                                            ->unique(ignoreRecord: true),
                                    ]),
                                Forms\Components\FileUpload::make('image')
                                    ->label('Product Image')
                                    // ->mediaLibraryCollection('image')
                                    ->image()
                                    ->required(),
                                Forms\Components\Section::make('Inventory & Shipping')
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

                                        Forms\Components\Toggle::make('status')
                                            ->label(
                                                fn ($state) => $state ? 'Active' : 'Inactive'
                                            )
                                            ->helperText('This product will be hidden from all sales channels.'),

                                    ])->columns(2),
                                // Reference: TenantResource
                                Forms\Components\Section::make('Dynamic Attributes')
                                    ->schema([
                                        Forms\Components\Repeater::make('dynamic_attributes')
                                            ->schema([
                                                Forms\Components\TextInput::make('attribute_name')->required(),
                                                Forms\Components\TextInput::make('attribute_value')->required(),
                                            ])
                                            ->disableItemMovement()
                                            ->defaultItems(1)
                                            ->columns(2),
                                    ]),
                            ])->hiddenOn('create'),
                    ]),
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
                    MetaDataForm::make('Meta Data'),
                ])->columnSpan(1),
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
