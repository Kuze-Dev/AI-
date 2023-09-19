<?php

namespace App\FilamentTenant\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Filament\Resources\ServiceResource\RelationManagers;
use App\FilamentTenant\Resources\ServiceResource\Pages\CreateService;
use App\FilamentTenant\Resources\ServiceResource\Pages\EditService;
use App\FilamentTenant\Resources\ServiceResource\Pages\ListServices;
use App\FilamentTenant\Support\MetaDataForm;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Closure;
use Domain\Service\Enums\Taxonomy as ServiceTaxonomy;
use Domain\Service\Models\Service;
use Domain\Taxonomy\Models\Taxonomy;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Forms;
use Illuminate\Support\Arr;

class ServiceResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    protected static ?string $navigationGroup = 'Service Management';

    public static function form(Form $form): Form
    {
        $taxonomies = Taxonomy::with('taxonomyTerms')
            ->whereIn(
                'slug',
                [ServiceTaxonomy::SERVICES->value]
            )->get();
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Card::make([
                            Forms\Components\TextInput::make('name')
                                ->label(trans('Service Name'))
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
                        Forms\Components\Section::make('Pricing')
                            ->translateLabel()
                            ->schema([

                                Forms\Components\TextInput::make('price')
                                    ->translateLabel()
                                    ->mask(fn(Forms\Components\TextInput\Mask $mask) => $mask->money(
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
                                    ->dehydrateStateUsing(fn($state) => (float)$state)
                                    ->required(),
                            ]),
                        Forms\Components\Section::make('Associations')
//                            ->translateLabel()
                            ->schema([
                                Forms\Components\Group::make()
                                    ->schema([
                                        Forms\Components\Group::make()
                                            ->statePath('taxonomies')
                                            ->schema(
                                                fn() => $taxonomies->map(
                                                    fn(Taxonomy $taxonomy) => Forms\Components\Select::make($taxonomy->name)
                                                        ->statePath((string)$taxonomy->id)
                                                        ->multiple(
                                                            fn() => $taxonomy->slug === ServiceTaxonomy::SERVICES->value ? false : true
                                                        )
                                                        ->options(
                                                            $taxonomy->taxonomyTerms->sortBy('name')
                                                                ->mapWithKeys(fn(TaxonomyTerm $term) => [$term->id => $term->name])
                                                                ->toArray()
                                                        )
                                                        ->formatStateUsing(
                                                            fn(?Service $record) => $record?->taxonomyTerms->where('taxonomy_id', $taxonomy->id)
                                                                ->pluck('id')
                                                                ->toArray() ?? []
                                                        )
                                                        ->required()
                                                )->toArray()
                                            )
                                            ->dehydrated(false),
                                        Forms\Components\Hidden::make('taxonomy_terms')
                                            ->dehydrateStateUsing(fn(Closure $get) => Arr::flatten($get('taxonomies') ?? [], 1)),
                                    ])
                                    ->when(fn() => !empty($taxonomies->toArray())),
                            ]),
                    ])->columnSpan(2),
                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make('Status')
                        ->translateLabel()
                        ->schema([
                            Forms\Components\Toggle::make('status')
                                ->label(
                                    fn($state) => $state ? ucfirst(trans(\Domain\Service\Enums\Status::ACTIVE->value)) : ucfirst(trans(\Domain\Service\Enums\Status::INACTIVE->value))
                                )
//                                ->helperText('This product will be hidden from all sales channels.'),
                        ]),
                    MetaDataForm::make('Meta Data'),
                ])->columnSpan(1),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServices::route('/'),
            'create' => CreateService::route('/create'),
            'edit' => EditService::route('/{record}/edit'),
        ];
    }
}
